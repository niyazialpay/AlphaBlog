<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    public static function middleware(): array
    {
        return [
            'guest'
        ];
    }

    public function login(Request $request){
        if(Auth::check()){
            return redirect()->route('admin.index');
        }
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);
        $login = request()->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if(Auth::attempt([$fieldType => $request->login, 'password' => $request->password], true)){
            return response()->json(['status' => true, 'message' => __('user.login_request.success')]);
        }
        return response()->json(['status' => false, 'message' => __('user.login_request.warning')], 401);

    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login');
    }

    public function forgotPassword(){
        return view('panel.auth.passwords.reset');
    }

    public function resetPassword(Request $request){
        $request->validate(['login' => 'required']);

        $login = request()->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $response = Password::sendResetLink(
            $fieldType == 'email' ? ['email' => $login] : ['username' => $login]
        );

        if($response === Password::RESET_LINK_SENT){
            $status = true;
        }
        else{
            $status = false;
        }
        return response()->json(['status' => $status, 'message' => __($response)]);
    }

    public function showResetForm($token){
        return view('panel.auth.passwords.reset-form', ['token' => $token]);
    }

    public function reset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required',
            'password' => 'required|confirmed'
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if($response === Password::PASSWORD_RESET){
            $status = true;
        }
        else{
            $status = false;
        }

        return response()->json(['status' => $status, 'message' => __($response)]);
    }

}
