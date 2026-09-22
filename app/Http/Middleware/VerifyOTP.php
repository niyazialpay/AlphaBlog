<?php

namespace App\Http\Middleware;

use App\Support\Panel\Panel;
use App\Support\Panel\PanelMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->inExceptArray($request)) {
            return $next($request);
        } else {
            if (! Auth::guest()) {
                $user = Auth::user();

                if ($user->getAttributeValue('otp') || $user->getAttributeValue('webauthn')) {
                    try {
                        if (session()->has('otp') && session('otp')) {
                            return $next($request);
                        } else {
                            return $this->otpChallenge($request);
                        }
                    } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                        return $this->otpChallenge($request);
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

    private function otpChallenge(Request $request): Response
    {
        $props = $this->UserHasWebAuthnOrTOTP();
        $inertiaProps = [
            ...$props,
            'nickname' => Auth::user()->nickname,
            'username' => Auth::user()->username,
            'profileImage' => replaceCDN(Auth::user()->profile_image).'&s=128',
            'honeypot' => Panel::honeypot(),
        ];

        if ($request->inertia()) {
            return Inertia::render('Auth/Otp', $inertiaProps)->toResponse($request)->setStatusCode(200);
        }

        if (Panel::vueEnabled('Auth/Otp') && Panel::isPanelRequest($request) && self::authScreensMigrated()) {
            return Inertia::render('Auth/Otp', $inertiaProps)->toResponse($request)->setStatusCode(200);
        }

        return response()->view('panel.auth.otp', $props);
    }

    private static function authScreensMigrated(): bool
    {
        return PanelMenu::isInertia('login');
    }

    private function UserHasWebAuthnOrTOTP(): array
    {
        $webauthn = WebAuthnCredential::where('authenticatable_id', Auth::user()->id)->count() > 0;
        $totp = Auth::user()->two_factor_confirmed_at;

        return ['webauthn' => $webauthn, 'totp' => $totp];
    }
}
