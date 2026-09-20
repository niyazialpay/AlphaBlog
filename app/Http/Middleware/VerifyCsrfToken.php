<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

/**
 * Uygulamanin CSRF middleware'i.
 *
 * ONCEDEN: Laravel 10'un `ValidateCsrfToken` sinifinin tam kopyasiydi ve
 * `bootstrap/app.php` icinde web grubunun BASINA prepend ediliyordu. Iki sonuc:
 *
 *  1. `StartSession` de prepend edildigi icin (ayni dosyada) oturum
 *     `EncryptCookies`'ten ONCE baslatiliyordu — oturum cerezi sifrelenmeden
 *     yaziliyor/okunuyordu ve `XSRF-TOKEN` cerezi de sifrelenmemis kaliyordu.
 *     Sunucu ise `X-XSRF-TOKEN` basligini decrypt etmeye calisiyor, `DecryptException`
 *     yiyor ve token'i bos string kabul ediyordu. Yani cerez yolu tamamen oluydu;
 *     yalnizca sayfa yuklenirken basilan statik `X-CSRF-TOKEN` basligi calisiyordu.
 *     O baslik bayatladigi anda (oturum yenilenmesi, SPA'nin acik kalmasi) her
 *     POST 419 donuyordu.
 *
 *  2. Laravel 13'un kendi `PreventRequestForgery` middleware'i grupta AYRICA
 *     duruyordu; `Sec-Fetch-Site: same-origin` kontrolu bu kopyada yoktu.
 *
 * SIMDI: framework middleware'i genisletilir ve `bootstrap/app.php` icinde
 * `replace` ile onun yerine konur. Sira kanonik Laravel sirasina doner, koruma
 * hem origin hem token tabanli olur, muaf yollar aynen korunur.
 */
class VerifyCsrfToken extends PreventRequestForgery
{
    /**
     * Dogrulamadan muaf URI'ler.
     *
     * WebAuthn: tarayici `navigator.credentials` akisi form token'i tasimaz.
     * EDergi track: modulun sayac ucu, oturumsuz cagrilir.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/webauthn/*',
        '*/edergi/track/*',
    ];
}
