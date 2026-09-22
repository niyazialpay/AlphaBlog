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

        if (! auth()->validate($credentials)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $user = auth()->getProvider()->retrieveByCredentials($credentials);

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
