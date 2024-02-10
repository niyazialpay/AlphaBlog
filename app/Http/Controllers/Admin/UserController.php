<?php

namespace App\Http\Controllers\Admin;

use App\Action\SocialNetworkSaveAction;
use App\Action\UserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserRequest;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(){
        return view("panel.auth.login");
    }

    public function index(){
        return view("panel.profile.index", [
            'user' => auth()->user()
        ]);
    }

    public function changePassword(PasswordRequest $request){
        $user = auth()->user();
        if(!Hash::check($request->old_password, $user->password)){
            return response()->json([
                'status' => 'error',
                'message' => __('profile.old_password_incorrect')
            ], 422);
        }
        UserAction::changePassword($request, $user);
        return response()->json([
            'status' => 'success',
            'message' => __('profile.password_change_success')
        ], 200);
    }

    public function userPasswordChange(Request $request, User $user){
        UserAction::changePassword($request, $user);
        return response()->json([
            'status' => 'success',
            'message' => __('profile.password_change_success')
        ], 200);
    }

    public function save(UserRequest $request){
        return UserAction::userSave($request, auth()->user());
    }

    private function socialProfileSave($request, $user_id){
        if(SocialNetworkSaveAction::execute($request, 'user', $user_id)){
            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success')
            ], 200);
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error')
            ], 422);
        }
    }

    public function socialSave(Request $request){
        return $this->socialProfileSave($request, auth()->id());
    }

    public function userSocialSave(Request $request, User $user){
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
        return UserAction::userSave($request, $user);
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
