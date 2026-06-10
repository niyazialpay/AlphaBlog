<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SessionAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebAuthnCredential;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use MaxMind\Db\Reader\InvalidDatabaseException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

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
            // SECURITY: do NOT echo email/username here. This endpoint is reachable
            // pre-authentication; returning PII enables account enumeration and
            // address harvesting. The opaque 'login' handle is sufficient for the
            // subsequent WebAuthn assertion.
            return [
                'status' => true,
                'webauthn' => true,
                'login' => hash('sha512', $check->email.$check->id.$check->username),
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

    /**
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
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
            // SECURITY: rotate the session ID on privilege change to prevent
            // session fixation. Must happen BEFORE sessionUpdate() so the tracked
            // user_sessions row records the new session id.
            $request->session()->regenerate();
            if (Hash::needsRehash(auth()->user()->password)) {
                auth()->user()->password = Hash::make($request->password);
                auth()->user()->save();
            }
            SessionAction::sessionUpdate($request);

            return response()->json([
                'status' => true,
                'webauthn' => false,
                'message' => __('user.login_request.success'),
            ]);
        }

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            Log::warning('Login lockout triggered', [
                'username' => $request->username,
                'ip' => $request->ip(),
            ]);
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        $this->incrementLoginAttempts($request);

        // SECURITY: record failed authentications for monitoring/alerting.
        Log::warning('Failed login attempt', [
            'username' => $request->username,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'status' => false,
            'webauthn' => false,
            'message' => __('user.login_request.warning'),
        ], 401);

    }

    public function logout(Request $request)
    {
        Auth::logout();
        // SECURITY: fully invalidate the session and issue a fresh CSRF token on
        // logout so the old session id/token cannot be replayed.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
