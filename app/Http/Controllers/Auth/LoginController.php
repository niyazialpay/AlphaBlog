<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SessionAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebAuthnCredential;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use MaxMind\Db\Reader\InvalidDatabaseException;

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
            'guest',
        ];
    }

    private function checkWebAuthn($login)
    {

        $check = User::where('username', $login)->first();

        if ($check?->webauthn || WebAuthnCredential::where('authenticatable_id', $check?->id)->exists()) {
            return [
                'status' => true,
                'webauthn' => true,
                'login' => hash('sha512', $check->email.$check->id.$check->username),
                'username' => $check->username,
                'email' => $check->email,
            ];
        }

        return [
            'status' => true,
            'webauthn' => false,
            'login' => false,
        ];
    }

    public function loginFirst(Request $request)
    {
        $login = request()->input('username');

        return response()->json($this->checkWebAuthn($login));
    }

    public function login(Request $request)
    {
        $login = request()->input('login');

        $check_webauthn = $this->checkWebAuthn($login);
        if ($check_webauthn['status'] && $check_webauthn['webauthn']) {
            return response()->json($this->checkWebAuthn($login));
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], true)) {
            if (Hash::needsRehash(auth()->user()->password)) {
                auth()->user()->password = Hash::make($request->password);
                auth()->user()->save();
            }
            SessionAction::sessionUpdate($request);
            return response()->json([
                'status' => false,
                'webauthn' => false,
                'message' => __('user.login_request.success'),
            ]);
        }

        return response()->json([
            'status' => false,
            'webauthn' => false,
            'message' => __('user.login_request.warning'),
        ], 401);

    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }
}
