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
            if ($request->has('role')) {
                $user->role = $request->role;
            }
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
