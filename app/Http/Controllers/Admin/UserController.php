<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(){
        return view("auth.login");
    }

    public function index(){
        return view("panel.profile.index");
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

    public function save(UserRequest $request){
        $user = auth()->user();
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->nickname = $request->nickname;
        $user->location = $request->location;
        $user->about = $request->about;
        $user->website = $request->website;
        $user->education = $request->education;
        $user->job_title = $request->job_title;
        $user->skills = $request->skills;
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => __('profile.save_success')
        ], 200);
    }
}
