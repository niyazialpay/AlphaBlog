<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SocialNetworkSaveAction;
use App\Actions\UserAction;
use App\Actions\WebAuthnAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPasswordRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ProfileImageRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserRequest;
use App\Models\ProfilePrivacy;
use App\Models\User;
use App\Models\UserSessions;
use App\Models\WebAuthnCredential;
use App\Observers\UserObserver;
use App\Support\Notifications\NotificationEvents;
use App\Support\Panel\Panel;
use App\Support\Panel\PanelResponse;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use LaravelIdea\Helper\App\Models\_IH_User_C;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UserController extends Controller
{
    public function login(): SymfonyResponse
    {
        if (auth()->check()) {
            return redirect()->route('admin.index');
        }

        return PanelResponse::render(
            'Auth/Login',
            'panel.auth.login',
            [
                'honeypot' => Panel::honeypot(),
                'routes' => [
                    'firstStep' => route('login.first_step'),
                    'login' => route('login'),
                    'forgotPassword' => route('forgot-password'),
                    'dashboard' => route('admin.index'),
                    'webauthnOptions' => route('webauthn.login.options'),
                    'webauthnLogin' => route('webauthn.login'),
                ],
            ],
        );
    }

    public function index(): SymfonyResponse
    {
        $user = auth()->user();
        $sessions = $user->sessions()
            ->join('sessions', 'user_sessions.session_id', '=', 'sessions.id')
            ->orderBy('sessions.last_activity', 'DESC')
            ->select('user_sessions.*', 'sessions.last_activity')
            ->paginate(10);

        return $this->profileResponse($user, $sessions, true);
    }

    public function changePassword(PasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();
        if (! Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => __('profile.old_password_incorrect'),
            ]);
        }
        UserAction::changePassword($request, $user);

        return back()->with('success', __('profile.password_change_success'));
    }

    public function userPasswordChange(AdminPasswordRequest $request, User $user_id): RedirectResponse
    {
        $ranks = $this->roleRanks();
        $actorRank = $ranks[auth()->user()->role] ?? -1;
        $targetRank = $ranks[$user_id->role] ?? PHP_INT_MAX;
        abort_unless($targetRank < $actorRank, 403);

        UserAction::changePassword($request, $user_id);

        return back()->with('success', __('profile.password_change_success'));
    }

    public function save(UserRequest $request)
    {
        return UserAction::userSave($request, auth()->user());
    }

    private function socialProfileSave($request, $user_id): RedirectResponse
    {
        if (SocialNetworkSaveAction::execute($request, 'user', $user_id)) {
            return back()->with('success', __('profile.save_success'));
        }

        return back()->with('error', __('profile.save_error'));
    }

    public function socialSave(Request $request): RedirectResponse
    {
        return $this->socialProfileSave($request, auth()->id());
    }

    public function userSocialSave(Request $request, User $user_id): RedirectResponse
    {
        return $this->socialProfileSave($request, $user_id->id);
    }

    public function userList(Request $request)
    {
        $query = User::where('id', '!=', auth()->id());

        if ($request->has('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('surname', 'like', '%'.$request->search.'%')
                    ->orWhere('nickname', 'like', '%'.$request->search.'%')
                    ->orWhere('username', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->orderBy('created_at', 'DESC')->paginate(10)->withQueryString();

        return PanelResponse::render(
            'Users/Index',
            'panel.user.index',
            [
                'users' => PanelResponse::rows($users, fn (User $item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'surname' => $item->surname,
                    'nickname' => $item->nickname,
                    'username' => $item->username,
                    'email' => $item->email,
                    'role' => $item->role,
                    'avatar' => replaceCDN($item->profile_image),
                    'createdAt' => $item->created_at?->toIso8601String(),
                ]),
                'filters' => ['search' => $request->get('search')],
                'assignableRoles' => array_values(array_filter(
                    array_keys($this->roleRanks()),
                    fn (string $role) => $this->canAssignRole(auth()->user(), $role),
                )),
            ],
            ['users' => $users],
        );
    }

    public function userEdit(User $user_id): SymfonyResponse
    {
        $sessions = $user_id->sessions()->orderBy('created_at', 'DESC')->paginate(10);

        return $this->profileResponse($user_id, $sessions, false);
    }

    /**
     * @param  LengthAwarePaginator  $sessions
     */
    private function profileResponse(User $user, $sessions, bool $isSelf): SymfonyResponse
    {
        $social = $user->social;

        return PanelResponse::render(
            'Profile/Index',
            'panel.profile.index',
            [
                'isSelf' => $isSelf,
                'profile' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'nickname' => $user->nickname,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'location' => $user->location,
                    'about' => $user->about,
                    'education' => $user->education,
                    'job_title' => $user->job_title,
                    'skills' => $user->skills,
                    'avatar' => replaceCDN($user->profile_image),
                    'otp' => (bool) $user->getAttributeValue('otp'),
                    'webauthn' => (bool) $user->getAttributeValue('webauthn'),
                    'two_factor_confirmed' => (bool) $user->two_factor_confirmed_at,
                ],
                'twoFactor' => $isSelf ? [
                    'confirmed' => (bool) $user->two_factor_confirmed_at,
                    'pending' => (bool) ($user->two_factor_secret && ! $user->two_factor_confirmed_at),
                    'secretKey' => ($user->two_factor_secret && ! $user->two_factor_confirmed_at)
                        ? decrypt($user->two_factor_secret)
                        : null,
                    'qrCodeSvg' => ($user->two_factor_secret && ! $user->two_factor_confirmed_at)
                        ? 'data:image/svg+xml;base64,'.base64_encode($user->twoFactorQrCodeSvg())
                        : null,
                    'recoveryCodes' => $user->two_factor_confirmed_at ? $user->recoveryCodes() : [],
                ] : null,
                'social' => collect(self::SOCIAL_FIELDS)
                    ->mapWithKeys(fn (string $field) => [$field => $social->{$field} ?? ''])
                    ->all(),
                'privacy' => collect(self::PRIVACY_FIELDS)
                    ->mapWithKeys(fn (string $field) => [$field => (bool) ($user->privacy->{$field} ?? false)])
                    ->all(),
                'sessions' => PanelResponse::rows($sessions, fn ($session) => [
                    'id' => $session->id,
                    'ip' => $session->ip_address ?? null,
                    'user_agent' => $session->user_agent ?? null,
                    'lastActivity' => isset($session->last_activity)
                        ? now()->setTimestamp((int) $session->last_activity)->toIso8601String()
                        : null,
                    'createdAt' => $session->created_at?->toIso8601String(),
                ]),
                'assignableRoles' => array_values(array_filter(
                    array_keys($this->roleRanks()),
                    fn (string $role) => $this->canAssignRole(auth()->user(), $role),
                )),
                'notificationEvents' => $isSelf ? $this->notificationEvents($user) : [],
            ],
            ['user' => $user, 'sessions' => $sessions],
        );
    }

    /**
     * @return list<array{key: string, label: string, database: bool, push: bool}>
     */
    private function notificationEvents(User $user): array
    {
        $saved = $user->notificationPreferences()->get()->keyBy('event');

        return collect(NotificationEvents::forUser($user))
            ->map(function (array $definition, string $event) use ($saved): array {
                $defaults = NotificationEvents::defaults($event);
                $preference = $saved->get($event);

                return [
                    'key' => $event,
                    'label' => __($definition['label']),
                    'database' => $preference !== null
                        ? (bool) $preference->database
                        : (bool) $defaults['database'],
                    'push' => $preference !== null
                        ? (bool) $preference->push
                        : (bool) $defaults['push'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @var list<string>
     */
    private const SOCIAL_FIELDS = [
        'linkedin', 'facebook', 'x', 'bluesky', 'instagram', 'github', 'devto',
        'medium', 'youtube', 'reddit', 'xbox', 'deviantart', 'website', 'twitch',
        'telegram', 'discord',
    ];

    /**
     * @var list<string>
     */
    private const PRIVACY_FIELDS = [
        'show_name', 'show_surname', 'show_location', 'show_education',
        'show_job_title', 'show_skills', 'show_about', 'show_social_links',
    ];

    public function userUpdate(Request $request, User $user_id)
    {
        if ($request->has('role') && $request->role !== $user_id->role) {
            if (! $this->canAssignRole(auth()->user(), $request->role)) {
                return back()->with('error', __('profile.save_error'));
            }
            $user_id->role = $request->role;
            $user_id->save();
        }

        return UserAction::userSave($request, $user_id);
    }

    /**
     * @return array<string, int>
     */
    private function roleRanks(): array
    {
        return ['user' => 0, 'author' => 1, 'editor' => 2, 'admin' => 3, 'owner' => 4];
    }

    private function canAssignRole(User $actor, string $targetRole): bool
    {
        $ranks = $this->roleRanks();
        if (! array_key_exists($targetRole, $ranks)) {
            return false;
        }
        if ($actor->role === 'owner') {
            return true;
        }

        return $ranks[$targetRole] < ($ranks[$actor->role] ?? -1);
    }

    public function create(): SymfonyResponse
    {
        return PanelResponse::render(
            'Users/Create',
            'panel.user.create',
            [
                'assignableRoles' => array_values(array_filter(
                    array_keys($this->roleRanks()),
                    fn (string $role) => $this->canAssignRole(auth()->user(), $role),
                )),
            ],
            [],
        );
    }

    public function store(UserCreateRequest $request, User $user): RedirectResponse
    {
        if (! $this->canAssignRole(auth()->user(), (string) $request->role)) {
            return back()->with('error', __('profile.save_error'));
        }

        try {
            DB::beginTransaction();
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->nickname = $request->nickname;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->password = Hash::make($request->password);
            $user->save();

            ProfilePrivacy::create([
                'user_id' => $user->id,
            ]);

            DB::commit();

            $warning = UserObserver::$emailFailed
                ? __('user.email_verification_failed')
                : null;

            return to_route('admin.users')
                ->with('success', __('profile.save_success'))
                ->with('warning', $warning);
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', __('profile.save_error'));
        }
    }

    public function userDelete(Request $request, User $user): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $user::where('id', $request->user_id)->delete();
            DB::commit();

            return back()->with('success', __('profile.delete_success'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', __('profile.delete_error'));
        }
    }

    public function webauthnList(User $user_id)
    {
        return response()->json($user_id->WebAuthn);
    }

    public function webauthnDelete(Request $request, WebAuthnCredential $webauthn, User $user_id): JsonResponse
    {
        return (new WebAuthnAction)->delete($request, $webauthn, $user_id);
    }

    public function webauthnRename(Request $request, WebAuthnCredential $webauthn, User $user_id): JsonResponse
    {
        return (new WebAuthnAction)->rename($request, $webauthn, $user_id);
    }

    public function userEmailChange(Request $request, User $user_id): RedirectResponse
    {
        if (UserAction::changeEmail($request, $user_id)) {
            return back()->with('success', __('profile.save_success'));
        } else {
            return back()->with('error', __('profile.save_error'));
        }
    }

    public function changeEmail(Request $request): RedirectResponse
    {
        if (UserAction::changeEmail($request, auth()->user())) {
            return back()->with('success', __('profile.save_success'));
        } else {
            return back()->with('error', __('profile.save_error'));
        }
    }

    public function privacy(Request $request): RedirectResponse
    {
        if ($request->has('show_name')) {
            $show_name = true;
        } else {
            $show_name = false;
        }

        if ($request->has('show_surname')) {
            $show_surname = true;
        } else {
            $show_surname = false;
        }

        if ($request->has('show_location')) {
            $show_location = true;
        } else {
            $show_location = false;
        }

        if ($request->has('show_education')) {
            $show_education = true;
        } else {
            $show_education = false;
        }

        if ($request->has('show_job_title')) {
            $show_job_title = true;
        } else {
            $show_job_title = false;
        }

        if ($request->has('show_skills')) {
            $show_skills = true;
        } else {
            $show_skills = false;
        }

        if ($request->has('show_about')) {
            $show_about = true;
        } else {
            $show_about = false;
        }

        if ($request->has('show_social_links')) {
            $show_social_links = true;
        } else {
            $show_social_links = false;
        }

        if (auth()->user()->role == 'owner' || auth()->user()->role == 'admin') {
            if ($request->has('user_id')) {
                $user_id = $request->user_id;
            } else {
                $user_id = auth()->id();
            }
        } else {
            $user_id = auth()->id();
        }
        ProfilePrivacy::updateOrCreate(
            ['user_id' => $user_id],
            [
                'show_name' => $show_name,
                'show_surname' => $show_surname,
                'show_location' => $show_location,
                'show_education' => $show_education,
                'show_job_title' => $show_job_title,
                'show_skills' => $show_skills,
                'show_about' => $show_about,
                'show_social_links' => $show_social_links,
            ]
        );

        return back()->with('success', __('profile.save_success'));
    }

    public function userSecretLogin($user_id)
    {
        $target = User::find($user_id);
        if (! $target) {
            abort(404);
        }

        $ranks = $this->roleRanks();
        $actorRank = $ranks[auth()->user()->role] ?? -1;
        $targetRank = $ranks[$target->role] ?? PHP_INT_MAX;
        abort_unless($targetRank < $actorRank, 403);

        $originalUserId = Auth::id();
        session()->put(['impersonated' => $user_id]);
        session()->put(['impersonated_original' => $originalUserId]);
        Auth::loginUsingId($user_id);

        return redirect()->route('admin.index');
    }

    public function secretLogout(Request $request)
    {
        if (Session::has('impersonated')) {
            $originalUserId = Session::get('impersonated_original');
            Session::forget('impersonated');
            Session::forget('impersonated_original');
            if ($originalUserId) {
                Auth::loginUsingId($originalUserId);
            }
        }

        return redirect()->route('admin.index');
    }

    public function killSession(Request $request): RedirectResponse
    {
        $session = UserSessions::find($request->session_id);
        if (! $session) {
            abort(404);
        }

        $isPrivileged = in_array(auth()->user()->role, ['owner', 'admin'], true);
        abort_unless($isPrivileged || $session->user_id === auth()->id(), 403);

        $session->session()->delete();
        $session->delete();

        return back()->with('success', __('profile.delete_success'));
    }

    public function killAllSession(Request $request)
    {
        if ($request->has('user_id')) {
            if (auth()->user()->role == 'owner' || auth()->user()->role == 'admin') {
                $user_id = $request->user_id;
            } else {
                $user_id = auth()->id();
            }
        } else {
            $user_id = auth()->id();
        }
        $sessions = UserSessions::join('sessions', 'user_sessions.session_id', '=', 'sessions.id')
            ->orderBy('sessions.last_activity', 'DESC')
            ->select('user_sessions.*', 'sessions.last_activity')->whereNot('sessions.id', session()->getId())->where('user_sessions.user_id', $user_id)->get();
        foreach ($sessions as $session) {
            $session->session()->delete();
            $session->delete();
        }

        return back()->with('success', __('user.all_sessions_ended'));
    }

    /**
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function profileImage(ProfileImageRequest $request)
    {
        $user = $this->extracted($request);
        $ext = $request->file('profile_image')->getClientOriginalExtension();
        $user->addMediaFromRequest('profile_image')
            ->usingFileName($user->username.'.'.$ext)
            ->toMediaCollection('profile');
        if ($user->save()) {
            return back()->with('success', __('profile.profile_image_uploaded'));
        } else {
            return back()->with('error', __('profile.profile_image_upload_error'));
        }
    }

    public function deleteProfilImage(Request $request)
    {
        $this->extracted($request);

        return back()->with('success', __('profile.profile_image_deleted'));
    }

    /**
     * @return User|User[]|_IH_User_C|null
     */
    public function extracted(Request $request)
    {
        if ($request->has('user_id')) {
            if (auth()->user()->role == 'owner' || auth()->user()->role == 'admin') {
                $user_id = $request->user_id;
            } else {
                $user_id = auth()->id();
            }
        } else {
            $user_id = auth()->id();
        }

        $user = User::find($user_id);
        if ($user->getFirstMedia('profile')) {
            $user->getFirstMedia('profile')->delete();
        }

        return $user;
    }
}
