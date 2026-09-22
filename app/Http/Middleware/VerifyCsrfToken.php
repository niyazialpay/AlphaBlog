<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

class VerifyCsrfToken extends PreventRequestForgery
{
    /**
     * @var array<int, string>
     */
    protected $except = [
        '/webauthn/*',
        '*/edergi/track/*',
    ];
}
