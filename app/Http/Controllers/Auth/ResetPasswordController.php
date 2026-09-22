<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Providers\RouteServiceProvider;
use App\Support\Panel\Panel;
use App\Support\Panel\PanelResponse;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected string $redirectTo = RouteServiceProvider::HOME;

    public function resetPassword(Request $request)
    {
        $request->validate(['login' => 'required']);
        $login = request()->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($fieldType, $login)->first();
        $user?->notify(new ResetPassword($this->broker()->createToken($user).'?user='.urlencode($login)));

        return response()->json(['status' => true, 'message' => __('auth.reset_password.reset_password_send')]);
    }

    public function showResetForm($token): SymfonyResponse
    {
        return PanelResponse::render(
            'Auth/Passwords/Reset',
            'panel.auth.passwords.reset-form',
            [
                'token' => $token,
                'user' => (string) request()->get('user'),
                'honeypot' => Panel::honeypot(),
                'routes' => [
                    'passwordUpdate' => route('password.update'),
                    'dashboard' => route('admin.index'),
                ],
            ],
            ['token' => $token],
        );
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

    public function forgotPassword(): SymfonyResponse
    {
        return PanelResponse::render(
            'Auth/Passwords/Email',
            'panel.auth.passwords.reset',
            [
                'honeypot' => Panel::honeypot(),
                'routes' => [
                    'forgotPassword' => route('forgot-password'),
                    'login' => route('login'),
                ],
            ],
        );
    }
}
