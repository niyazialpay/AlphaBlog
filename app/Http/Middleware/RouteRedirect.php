<?php

namespace App\Http\Middleware;

use App\Models\RouteRedirects;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RouteRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route_path = $request->path().($request->getQueryString() ? '?'.$request->getQueryString() : '');
        if(Cache::has(config('cache.prefix').'routes_'.Str::slug($route_path))){
            $route = Cache::get(config('cache.prefix').'routes_'.Str::slug($route_path));
        }
        else{
            $route = Cache::rememberForever(config('cache.prefix').'route_'.Str::slug($request->path()), function()use($route_path){
                return RouteRedirects::where('old_url', $route_path)->first();
            });
        }
        if ($route) {
            if($route->redirect_code == 404) {
                abort(404);
            }
            else{
                return redirect($route->new_url, (int)$route->redirect_code);
            }
        }
        return $next($request);
    }
}
