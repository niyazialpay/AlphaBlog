<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPostType
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (($request->route()->parameter('type') == 'pages') ||
            ($request->route()->parameter('type') == 'blogs')) {
            return $next($request);
        } else {
            abort(404);
        }

    }
}
