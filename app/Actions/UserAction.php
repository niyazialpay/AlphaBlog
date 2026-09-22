<?php

namespace App\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserAction
{
    public static function userSave($request, $user): JsonResponse|RedirectResponse
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
            $user->save();
            DB::commit();

            return back()->with('success', __('profile.save_success'));
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', __('profile.save_error'));
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
