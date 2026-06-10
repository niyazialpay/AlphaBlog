<?php

namespace App\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAction
{
    public static function userSave($request, $user): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->nickname = $request->nickname;
            $user->location = $request->location;
            $user->about = $request->about;
            $user->education = $request->education;
            $user->job_title = $request->job_title;
            $user->skills = $request->skills;
            // SECURITY: 'role' is intentionally NOT assignable here. Profile/user-edit
            // saves must never escalate privileges. Role changes go through the
            // dedicated, ceiling-checked path in UserController::userUpdate().
            $user->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => __('profile.save_success'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => __('profile.save_error'),
            ], 422);
        }
    }

    public static function changePassword($request, $user): bool
    {
        $user->password = Hash::make($request->password);

        return $user->save();
    }

    public static function changeEmail($request, $user): bool
    {
        $user->email = $request->email;

        return $user->save();
    }
}
