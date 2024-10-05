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

        if ($filter->count() == 0) {
            return $next($request);
        }

        $blacklisted_ips = [];
        $whitelisted_ips = [];
        $blacklisted_route_list = [];
        $whitelisted_route_list = [];

        $block_code = 403;

        foreach ($filter as $filter_item) {
            if ($request->is($filter_item->routeList->pluck('route')->toArray())) {
                if ($filter_item->list_type == 'blacklist') {
                    $blacklisted_ips = array_merge($blacklisted_ips, $filter_item->ipList->pluck('ip')->toArray());
                    $blacklisted_route_list = array_merge($blacklisted_route_list, $filter_item->routeList->pluck('route')->toArray());
                } else {
                    $whitelisted_ips = array_merge($whitelisted_ips, $filter_item->ipList->pluck('ip')->toArray());
                    $whitelisted_route_list = array_merge($whitelisted_route_list, $filter_item->routeList->pluck('route')->toArray());

                }
                $block_code = $filter_item->code;
            }
        }

        if (count($blacklisted_ips) > 0) {
            if (IpUtils::checkIp($request->getClientIp(), $blacklisted_ips) && $request->is($blacklisted_route_list)) {
                abort($block_code);
            }
        }

        if (count($whitelisted_ips) > 0) {
            if (IpUtils::checkIp($request->getClientIp(), $whitelisted_ips) && $request->is($whitelisted_route_list)) {
                return $next($request);
            } else {
                abort($block_code);
            }
        }

        return $next($request);
    }
}
