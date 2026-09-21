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
            // SECURITY: 'role' is intentionally NOT assignable here. Profile/user-edit
            // saves must never escalate privileges. Role changes go through the
            // dedicated, ceiling-checked path in UserController::userUpdate().
            $user->save();
            DB::commit();

            /*
             * TEK sekil: yonlendirme.
             *
             * Eskiden `$request->inertia()` ile dallaniyordu. O kontrol yaniti
             * X-Inertia basliginin agda sag kalmasina bagliyor; baslik dustugunde
             * sunucu JSON donuyor, Inertia JSON'i sayfa sayamayip tam ekran hata
             * modalini aciyor ve kullanici bembeyaz bir kutu goruyordu. Profil
             * kaydetme bir FORM eylemi, veri ucu degil.
             */
            return back()->with('success', __('profile.save_success'));
        } catch (Throwable $e) {
            // `Exception` DEGIL: TypeError / null uzerinde metot cagrisi `Error`
            // sinifindan gelir, `Exception` onu yakalamaz ve transaction acik kalirdi.
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
