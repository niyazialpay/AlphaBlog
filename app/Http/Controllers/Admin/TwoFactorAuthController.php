<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

class TwoFactorAuthController extends Controller
{
    public function confirm(Request $request)
    {
        $confirmed = $this->confirm_verify($request);
        if (! $confirmed) {
            return response()->json(['status' => 'error', 'message' => __('user.two_fa.invalid_code')]);
        }

        return response()->json(['status' => 'success', 'message' => __('user.two_fa.code_verified')]);
    }

    public function verify(Request $request): JsonResponse
    {
        $confirmed = $this->confirm_verify($request);
        if (! $confirmed) {
            return response()->json(['status' => 'error', 'message' => __('user.two_fa.invalid_code')]);
        }
        if (session()->has('otp')) {
            session()->remove('otp');
        }
        session()->put('otp', true);

        return response()->json(['status' => 'success', 'message' => __('user.two_fa.code_verified')]);
    }

    private function confirm_verify($request)
    {
        return $request->user()->confirmTwoFactorAuth($request->code);
    }

    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse|RedirectResponse
    {
        $disable($request->user());

        $user = auth()->user();
        $user->otp = false;
        $user->two_factor_confirmed_at = null;
        $user->save();

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : back()->with('status', 'two-factor-authentication-disabled');
    }

    public function lock(): RedirectResponse
    {
        if (session()->has('otp')) {
            session()->remove('otp');
        }

        return redirect()->route('admin.index')->with('status', 'two-factor-authentication-locked');
    }
}
