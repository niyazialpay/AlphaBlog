<?php

namespace App\Actions;

use App\Models\RouteRedirects;
use App\Support\Panel\Panel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RouteRedirectAction
{
    public static function RouteRedirect($request)
    {
        /*
         * YONETIM PANELI YONLENDIRME TABLOSUNA TABI DEGIL.
         *
         * Bu eylem global `RouteRedirect` middleware'inden (ve exception
         * hook'undan) cagriliyor, yani HER istege bakiyor. Site icin tanimlanan
         * bir kural panel URL'iyle eslestiginde yonetim ekranini kaciriyor:
         * Inertia ziyareti sayfa yerine bir yonlendirme govdesi aliyor ve
         * kullaniciya bos/bozuk ekran donuyor.
         *
         * Panel yollari (ve panel disindaki auth ekranlari) bu tablodan muaf.
         */
        if (Panel::isPanelRequest($request)) {
            return null;
        }

        $route_path = $request->path().($request->getQueryString() ? '?'.$request->getQueryString() : '');
        if (Cache::has(config('cache.prefix').'routes_'.Str::slug($route_path))) {
            $route = Cache::get(config('cache.prefix').'routes_'.Str::slug($route_path));
        } else {
            $route = Cache::rememberForever(config('cache.prefix').'route_'.Str::slug($route_path),
                function () use ($route_path) {
                    return RouteRedirects::where('old_url', $route_path)->first();
                });
        }

        return $route;
    }
}
