<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'code' => 'nullable|string',
        ]);

        $credentials = $request->only('username', 'password');

        // Validate credentials WITHOUT establishing a session (token flow).
        if (! auth()->validate($credentials)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $user = auth()->getProvider()->retrieveByCredentials($credentials);

        // SECURITY: optionally enforce the second factor here too. The web flow
        // gates 2FA via VerifyOTP middleware; the API can issue a token from
        // password alone unless API_REQUIRE_2FA is enabled. Gated by a flag
        // (default off) so existing mobile/API clients that only send
        // username+password are not locked out — flip it on once clients send
        // the TOTP `code`.
        // The app tracks "2FA enabled" via the otp flag (set true by
        // User::confirmTwoFactorAuth, false on disable). two_factor_confirmed_at
        // is only migrated when Fortify's confirm feature is on, so otp is the
        // reliable signal here.
        $hasTwoFactor = $user->two_factor_secret && $user->otp;

        if (config('sanctum.require_two_factor') && $hasTwoFactor) {
            $code = (string) $request->input('code', '');
            if ($code === '' || ! $user->confirmTwoFactorAuth($code)) {
                return response()->json([
                    'message' => 'Two-factor authentication code required.',
                    'two_factor_required' => true,
                ], 423);
            }
        } elseif ($hasTwoFactor) {
            // AUDIT: a 2FA-enrolled user authenticated via the API from password
            // alone because API_REQUIRE_2FA is off (legacy-client compatibility).
            // Logged so this exposure is auditable and can be time-boxed; flip
            // API_REQUIRE_2FA=true once clients send the TOTP code.
            Log::warning('API login bypassed 2FA for a 2FA-enrolled user (API_REQUIRE_2FA off)', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('authToken', ['*'])->plainTextToken,
        ]);
    }
}
