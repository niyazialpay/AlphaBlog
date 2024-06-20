<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class DisableCookiesForCdn
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->getHost() === config('app.cdn_url') && $request->getHost() !== config('app.url')) {
            config(['session.driver' => 'array']);
            config(['session.cookie' => '']);
            Cookie::flushQueuedCookies();
        }

        return $next($request);
    }
}
