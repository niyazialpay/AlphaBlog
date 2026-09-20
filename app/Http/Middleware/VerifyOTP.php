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

                /*
                 * getAttributeValue() BILEREK kullaniliyor.
                 *
                 * User modelinde `WebAuthn(): HasMany` adinda bir iliski var ve PHP
                 * metod adlari buyuk/kucuk harf duyarsiz. `$user->webauthn` sutun
                 * ozniteliklerde yuklu degilse Eloquent iliskiye duser ve BOS bir
                 * Collection doner - PHP'de truthy. Bu da 2FA'si OLMAYAN kullanicilari
                 * OTP duvarina carpiyordu (kismi yuklu model her yerde: factory,
                 * ->select(...), yeni ornekler).
                 *
                 * getAttributeValue() yalnizca oznitelik/cast okur, iliskiye dusmez.
                 */
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

    /**
     * OTP duvari.
     *
     * Bu middleware cekirdek panel grubuna VE 7 modulun kendi route gruplarina
     * uygulanmis durumda. Duz `response()->view(...)` bir Inertia XHR'ina 200 +
     * HTML dondurur; client bunu Inertia yaniti sayamaz ve sonsuz yeniden yukleme
     * ya da hata modali uretir. Bu yuzden icerik muzakere edilir.
     *
     * 302 DEGIL 200 donulur: Inertia 200 + X-Inertia yanitinda bileseni dogru
     * sekilde takas eder ve mevcut URL korunur (bugunku davranisin aynisi).
     */
    private function otpChallenge(Request $request): Response
    {
        $props = $this->UserHasWebAuthnOrTOTP();
        $inertiaProps = [
            ...$props,
            'nickname' => Auth::user()->nickname,
            'username' => Auth::user()->username,
            'profileImage' => replaceCDN(Auth::user()->profile_image).'&s=128',
            // two-factor.verify ProtectAgainstSpam ile korunuyor: @honeypot karsiligi.
            'honeypot' => Panel::honeypot(),
        ];

        /*
         * Istek bir Inertia XHR'i ise karsisinda Vue paneli var demektir; Blade
         * gövdesi client'i kirar, bu yüzden Inertia yaniti sart.
         *
         * Tam sayfa yuklemeleri (Inertia XHR degil) Auth ekranlari tasinana kadar
         * eski Blade duvarini gormeye devam eder — modul ekranlari dahil.
         */
        if ($request->inertia()) {
            return Inertia::render('Auth/Otp', $inertiaProps)->toResponse($request)->setStatusCode(200);
        }

        if (Panel::vueEnabled('Auth/Otp') && Panel::isPanelRequest($request) && self::authScreensMigrated()) {
            return Inertia::render('Auth/Otp', $inertiaProps)->toResponse($request)->setStatusCode(200);
        }

        return response()->view('panel.auth.otp', $props);
    }

    /**
     * Auth ekranlari Vue'ya tasindi mi? Defter tek yerde: config/panel_inertia_routes.php.
     * Tasinmadan once tam sayfa yuklemeleri eski Blade duvarini gormeli.
     */
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
