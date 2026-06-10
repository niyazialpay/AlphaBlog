<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Default NULL (tokens never expire) to preserve existing behavior — Sanctum
    | measures expiry from each token's created_at, so setting a finite TTL would
    | RETROACTIVELY invalidate already-issued mobile/API tokens (mass logout).
    | Enable a TTL via SANCTUM_TOKEN_TTL (minutes) only after a token-refresh flow
    | exists / after rotating existing tokens.
    |
    */

    'expiration' => env('SANCTUM_TOKEN_TTL', null),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Require Two-Factor on API Login
    |--------------------------------------------------------------------------
    |
    | When true, POST /api/login refuses to issue a token for a 2FA-enabled
    | account unless a valid TOTP `code` is supplied (closes the API 2FA bypass).
    | Default FALSE to avoid breaking existing mobile/API clients that only send
    | username+password. Flip API_REQUIRE_2FA=true once clients send the code.
    |
    */

    'require_two_factor' => env('API_REQUIRE_2FA', false),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],

];
