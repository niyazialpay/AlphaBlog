<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SocialNetworkSaveAction;
use App\Actions\UserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserRequest;
use App\Models\ProfilePrivacy;
use App\Models\User;
use App\Models\WebAuthnCredential;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login()
    {
        return view('panel.auth.login');
    }

    public function index()
    {
        return view('panel.profile.index', [
            'user' => auth()->user(),
        ]);
    }

    public function changePassword(PasswordRequest $request)
    {
        $user = auth()->user();
        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => __('profile.old_password_incorrect'),
            ], 422);
        }
        UserAction::changePassword($request, $user);

        return response()->json([
            'status' => 'success',
            'message' => __('profile.password_change_success'),
        ], 200);
    }

    public function userPasswordChange(Request $request, User $user_id)
    {
        UserAction::changePassword($request, $user_id);

        return response()->json([
            'status' => 'success',
            'message' => __('profile.password_change_success'),
        ], 200);
    }

    public function save(UserRequest $request)
    {
        return UserAction::userSave($request, auth()->user());
    }

    private function socialProfileSave($request, $user_id)
    {
        if (SocialNetworkSaveAction::execute($request, 'user', $user_id)) {
            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success'),
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error'),
            ], 422);
        }
    }

    public function socialSave(Request $request)
    {
        return $this->socialProfileSave($request, auth()->id());
    }

    public function userSocialSave(Request $request, User $user_id)
    {
        return $this->socialProfileSave($request, $user_id->id);
    }

    public function userList()
    {
        return view('panel.user.index', [
            'users' => User::where('id', '!=', auth()->id())->orderBy('created_at', 'DESC')->paginate(10),
        ]);
    }

    public function userEdit(User $user_id)
    {
        return view('panel.profile.index', [
            'user' => $user_id,
        ]);
    }

    public function userUpdate(Request $request, User $user_id)
    {
        return UserAction::userSave($request, $user_id);
    }

    public function create()
    {
        return view('panel.user.create');
    }

    public function store(UserCreateRequest $request, User $user)
    {
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
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success'),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error'),
            ], 422);
        }
    }

    public function userDelete(Request $request, User $user)
    {
        try {
            DB::beginTransaction();
            $user::where('id', $request->user_id)->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('profile.delete_success'),
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => __('profile.delete_error'),
            ], 422);
        }
    }

    public function webauthnList(User $user_id)
    {
        return response()->json($user_id->WebAuthn);
    }

    public function webauthnDelete(Request $request, WebAuthnCredential $webauthn, User $user_id): JsonResponse
    {
        return (new \App\Actions\WebAuthnAction)->delete($request, $webauthn, $user_id);
    }

    public function webauthnRename(Request $request, WebAuthnCredential $webauthn, User $user_id): JsonResponse
    {
        return (new \App\Actions\WebAuthnAction)->rename($request, $webauthn, $user_id);
    }

    public function userEmailChange(Request $request, User $user_id)
    {
        if (UserAction::changeEmail($request, $user_id)) {
            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success'),
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error'),
            ], 422);

        }
    }

    public function changeEmail(Request $request)
    {
        if (UserAction::changeEmail($request, auth()->user())) {
            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success'),
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error'),
            ], 422);
        }
    }

    public function privacy(Request $request){
        if($request->has('show_name')){
            $show_name = true;
        }
        else{
            $show_name = false;
        }

        if($request->has('show_surname')){
            $show_surname = true;
        }
        else{
            $show_surname = false;
        }

        if($request->has('show_location')){
            $show_location = true;
        }
        else{
            $show_location = false;
        }

        if($request->has('show_education')){
            $show_education = true;
        }
        else{
            $show_education = false;
        }

        if($request->has('show_job_title')){
            $show_job_title = true;
        }
        else{
            $show_job_title = false;
        }

        if($request->has('show_skills')){
            $show_skills = true;
        }
        else{
            $show_skills = false;
        }

        if($request->has('show_about')){
            $show_about = true;
        }
        else{
            $show_about = false;
        }

        if($request->has('show_social_links')){
            $show_social_links = true;
        }
        else{
            $show_social_links = false;
        }

        if(auth()->user()->role == 'owner' || auth()->user()->role == 'admin'){
            if($request->has('user_id')){
                $user_id = $request->user_id;
            }
            else{
                $user_id = auth()->id();
            }
        }
        else{
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

        return response()->json([
            'status' => 'success',
            'message' => __('profile.save_success'),
        ], 200);
    }
}
