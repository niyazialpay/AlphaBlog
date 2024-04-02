<?php

namespace App\Http\Middleware;

use App\Action\RouteRedirectAction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RouteRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = RouteRedirectAction::RouteRedirect($request);
        if ($route) {
            if ($route->redirect_code == 404) {
                abort(404);
            } else {
                return redirect($route->new_url, (int) $route->redirect_code);
            }
        }

        return $next($request);
    }
}
