<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class IpFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        if (Cache::has(config('cache.prefix').'ip_filter')) {
            $filter = Cache::get(config('cache.prefix').'ip_filter');
        } else {
            $filter = Cache::rememberForever(config('cache.prefix').'ip_filter', function () {
                return \App\Models\IPFilter\IPFilter::with('ipList', 'routeList')->where('is_active', true)->get();
            });
        }

        $status = false;

        if ($filter->count() == 0) {
            $status = true;
        }
        foreach ($filter as $filter_item) {
            if (IpUtils::checkIp($request->getClientIp(), $filter_item->ipList->pluck('ip_range'))) {
                if ($request->is($filter_item->routeList->pluck('route'))) {
                    if ($filter_item->list_type == 'blacklist') {
                        $status = false;
                    } else {
                        $status = true;
                        break;
                    }
                } else {
                    $status = true;
                    break;
                }
            } else {
                if ($request->is($filter_item->routeList->pluck('routes'))) {
                    if ($filter_item->list_type == 'blacklist') {
                        $status = true;
                    } else {
                        $status = false;
                        break;
                    }
                } else {
                    $status = true;
                    break;
                }
            }
        }

        if ($status) {
            return $next($request);
        }
        abort(404);
    }
}
