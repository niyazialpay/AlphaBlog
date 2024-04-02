<?php

namespace App\Action;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserAction
{
    public static function userSave($request, $user): JsonResponse
    {
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->nickname = $request->nickname;
        $user->location = $request->location;
        $user->about = $request->about;
        $user->education = $request->education;
        $user->job_title = $request->job_title;
        $user->skills = $request->skills;
        if ($request->has('role')) {
            $user->role = $request->role;
        }
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('profile.save_success'),
        ], 200);
    }

    public static function changePassword($request, $user): bool
    {
        $user->password = Hash::make($request->password);

        return $user->save();
    }
}
