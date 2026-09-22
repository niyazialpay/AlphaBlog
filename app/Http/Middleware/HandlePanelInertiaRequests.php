<?php

namespace App\Http\Middleware;

use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Support\Panel\Panel;
use App\Support\Panel\PanelMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class HandlePanelInertiaRequests extends Middleware
{
    protected $rootView = 'panel.app';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Panel::isPanelRequest($request) || ! Panel::vueEnabled()) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $shared = [
            ...parent::share($request),

            'app' => ['name' => config('app.name')],
            'panelPath' => Panel::path(),
            'siteUrl' => config('app.url'),
            'timezone' => config('app.timezone'),
            'fontawesomePro' => (bool) config('settings.fontawesome_pro'),

            'siteName' => fn () => self::siteName(),
            'siteDomain' => parse_url((string) config('app.url'), PHP_URL_HOST),
            'favicon' => fn () => self::favicon(),
            'siteLogo' => fn () => self::siteLogo(),
            'turnstileSiteKey' => fn () => config('cloudflare.turnstile_site_key'),
            'aiEnabled' => fn () => PanelMenu::aiEnabled(),

            'inertiaRoutes' => PanelMenu::inertiaRoutePatterns(),

            'languages' => fn () => self::languages(),
            'currentLanguage' => fn () => [
                'code' => session('language'),
                'name' => session('language_name'),
                'flag' => session('language_flag'),
            ],
            'defaultLanguage' => fn () => self::defaultLanguage(),

            'csrfToken' => fn () => $request->session()->token(),

            'push' => fn () => [
                'enabled' => filled(config('webpush.public_key')) && filled(config('webpush.private_key')),
                'publicKey' => config('webpush.public_key'),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'message' => fn () => $request->session()->get('message'),
                'status' => fn () => $request->session()->get('status'),
            ],
        ];

        if ($user === null) {
            return [...$shared, 'auth' => ['user' => null], 'can' => [], 'counts' => [], 'menu' => []];
        }

        return [
            ...$shared,

            'auth' => fn () => ['user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'initials' => self::initials($user),
                'profileImage' => replaceCDN($user->profile_image),
                'hasTwoFactor' => (bool) ($user->webauthn || $user->otp),
                'impersonated' => $request->session()->has('impersonated'),
            ]],

            'can' => fn () => [
                'admin' => $user->can('admin', User::class),
                'owner' => $user->can('owner', User::class),
                'moderator' => $user->can('moderator', User::class),
                'cloudflare' => $user->can('cloudflare', User::class),
                'createPost' => $user->can('createPost', Posts::class),
                'viewPages' => $user->can('viewPages', Posts::class),
                'category' => $user->can('category', Categories::class),
                'viewComments' => $user->can('view', Comments::class),
            ],

            'counts' => fn () => [
                'newComments' => (int) View::shared('newCommentsCount', 0),
                'searchedWords' => (int) View::shared('searchedWordsCount', 0),
                'unreadNotifications' => (int) $user->unreadNotifications()->count(),
            ],

            'notifications' => Inertia::optional(fn () => $user->unreadNotifications()->take(5)->get()
                ->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? null,
                    'message' => $notification->data['message'] ?? null,
                    'url' => route('notifications.readAndRedirect', $notification->id),
                    'readAt' => $notification->read_at?->toIso8601String(),
                    'createdAt' => $notification->created_at?->toIso8601String(),
                    'ago' => $notification->created_at?->diffForHumans(),
                ])->all()),

            'menu' => fn () => PanelMenu::build($user),
        ];
    }

    private static function initials(User $user): string
    {
        $source = trim((string) $user->name.' '.(string) $user->surname) ?: (string) $user->nickname;

        return Str::of($source)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    private static function siteName(): ?string
    {
        try {
            return app('seo_settings')?->site_name ?? config('app.name');
        } catch (Throwable) {
            return config('app.name');
        }
    }

    /**
     * @return array{light: string|null, dark: string|null}
     */
    private static function siteLogo(): array
    {
        try {
            $settings = app('general_settings');

            return [
                'light' => $settings?->getFirstMediaUrl('site_logo_light') ?: null,
                'dark' => $settings?->getFirstMediaUrl('site_logo_dark') ?: null,
            ];
        } catch (Throwable) {
            return ['light' => null, 'dark' => null];
        }
    }

    private static function favicon(): ?string
    {
        try {
            return app('general_settings')?->getFirstMediaUrl('site_favicon') ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function languages(): array
    {
        try {
            return collect(app('languages'))
                ->map(fn ($language) => [
                    'code' => $language->code,
                    'name' => $language->name,
                    'flag' => $language->flag,
                ])
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private static function defaultLanguage(): ?string
    {
        try {
            return app('default_language')?->code;
        } catch (Throwable) {
            return null;
        }
    }
}
