<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    public function resetPassword(Request $request)
    {
        $request->validate(['login' => 'required']);
        $login = request()->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($fieldType, $login)->first();
        $user?->notify(new \App\Notifications\ResetPassword($this->broker()->createToken($user).'?user='.urlencode($login)));
        return response()->json(['status' => true, 'message' => __('auth.reset_password.reset_password_send')]);
    }

    public function showResetForm($token)
    {
        return view('panel.auth.passwords.reset-form', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'user' => 'required',
            'password' => 'required|confirmed',
        ]);

        $login = request()->input('user');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $request->merge([$fieldType => $login]);
        //$user = User::where($fieldType, $login)->first();

        $response = Password::reset(
            $request->only($fieldType, 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            $status = true;
        } else {
            $status = false;
        }

        return response()->json(['status' => $status, 'message' => __($response)]);
    }

    public function forgotPassword()
    {
        return view('panel.auth.passwords.reset');
    }
}
