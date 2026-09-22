<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Routing\Controllers\Middleware;

class VerificationController extends Controller
{
    use VerifiesEmails;

    protected string $redirectTo = RouteServiceProvider::HOME;

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('signed', only: ['verify']),
            new Middleware('throttle:6,1', only: ['verify', 'resend']),
        ];
    }
}
