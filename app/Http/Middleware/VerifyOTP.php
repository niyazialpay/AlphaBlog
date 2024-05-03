<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laragear\WebAuthn\Models\WebAuthnCredential;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class VerifyOTP
{
    protected array $except = [
        '/yubikey',
        '/webauthn/login/',
        '/webauthn/login/*',
        '/2fa-verify',
    ];

    protected function inExceptArray($request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->inExceptArray($request)) {
            return $next($request);
        } else {
            if (! Auth::guest()) {
                $user = Auth::user();
                if ($user->otp || $user->webauthn) {
                    try {
                        if (session()->has('otp') && session('otp')) {
                            return $next($request);
                        } else {
                            return response()->view('panel.auth.otp', $this->UserHasWebAuthnOrTOTP());
                        }
                    } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                        return response()->view('panel.auth.otp', $this->UserHasWebAuthnOrTOTP());
                    }
                } else {
                    return $next($request);
                }

            } else {
                if (session()->has('otp')) {
                    session()->remove('otp');
                }

                return $next($request);
            }
        }
    }

    private function UserHasWebAuthnOrTOTP(): array
    {
        $webauthn = WebAuthnCredential::where('authenticatable_id', Auth::user()->id)->count() > 0;
        $totp = Auth::user()->two_factor_confirmed_at;

        return ['webauthn' => $webauthn, 'totp' => $totp];
    }
}
