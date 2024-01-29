<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserPolicyRequest;
use App\Http\Requests\UserRequest;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\SocialNetworks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(){
        return view("auth.login");
    }

    public function index(){
        return view("panel.profile.index", [
            'user' => auth()->user()
        ]);
    }

    public function changePassword(PasswordRequest $request){
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);
        $user = auth()->user();
        if(!Hash::check($request->old_password, $user->password)){
            return response()->json([
                'status' => 'error',
                'message' => __('profile.old_password_incorrect')
            ], 422);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => __('profile.password_change_success')
        ], 200);
    }

    private function userSave($request, $user){
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->nickname = $request->nickname;
        $user->location = $request->location;
        $user->about = $request->about;
        $user->education = $request->education;
        $user->job_title = $request->job_title;
        $user->skills = $request->skills;
        if($request->has('role')){
            $user->role = $request->role;
        }
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => __('profile.save_success')
        ], 200);
    }

    public function save(UserRequest $request){
        return $this->userSave($request, auth()->user());
    }

    private function socialProfileSave($request, $user_id){
        $social = SocialNetworks::where('user_id', $user_id)->first();
        if(!$social){
            $social = new SocialNetworks();
            $social->user_id = $user_id;
        }
        $social->facebook = $request->facebook;
        $social->instagram = $request->instagram;
        $social->linkedin = $request->linkedin;
        $social->github = $request->github;
        $social->devto = $request->devto;
        $social->medium = $request->medium;
        $social->youtube = $request->youtube;
        $social->reddit = $request->reddit;
        $social->xbox = $request->xbox;
        $social->deviantart = $request->deviantart;
        $social->website = $request->website;
        $social->x = $request->x;
        $social->save();
        return response()->json([
            'status' => 'success',
            'message' => __('profile.save_success')
        ], 200);
    }

    public function socialSave(UserPolicyRequest $request){
        return $this->socialProfileSave($request, auth()->id());
    }

    public function userSocialSave(UserPolicyRequest $request, User $user){
        return $this->socialProfileSave($request, $user->id);
    }

    public function userList(){
        return view("panel.user.index", [
            'users' => User::where('_id', '!=', auth()->id())->orderBy('created_at', 'DESC')->paginate(10)
        ]);
    }

    public function userEdit(User $user){
        return view("panel.profile.index", [
            'user' => $user
        ]);
    }

    public function userUpdate(Request $request, User $user){
        return $this->userSave($request, $user);
    }



    public function create(){
        if(auth()->user()->cant('fullPermission', auth()->user())){
            abort(403);
        }
        return view("panel.user.create");
    }

    public function store(UserCreateRequest $request, User $user){
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->nickname = $request->nickname;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => __('profile.save_success')
        ], 200);
    }

    public function userDelete(Request $request, User $user){
        Posts::where('user_id', $request->user_id)->update(['user_id' => $user::where('role', 'owner')->first()->id]);
        Comments::where('user_id', $request->user_id)->update(['user_id' => $user::where('role', 'owner')->first()->id]);
        $user::where('_id', $request->user_id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => __('profile.delete_success')
        ], 200);
    }
}
