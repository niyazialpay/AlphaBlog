<?php

namespace App\Http\Middleware;

use App\Models\AdminOneSignal as OneSignal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AdminOneSignal
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Cache::has(config('cache.prefix').'admin_notification_settings')) {
            $onesignal = Cache::get(config('cache.prefix').'admin_notification_settings');
        } else {
            $onesignal = Cache::rememberForever(config('cache.prefix').'admin_notification_settings', function () {
                return OneSignal::first();
            });
        }
        View::share('admin_notification', $onesignal);

        return $next($request);
    }
}
