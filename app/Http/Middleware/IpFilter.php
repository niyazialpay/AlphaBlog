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
        // Retrieve filters from cache or database
        $filters = Cache::rememberForever(config('cache.prefix') . 'ip_filter', function () {
            return \App\Models\IPFilter\IPFilter::with('ipList', 'routeList')
                ->where('is_active', true)
                ->get();
        });

        if ($filters->isEmpty()) {
            return $next($request);
        }

        // Separate filters into those with route '*' and others
        $starFilters = $filters->filter(function ($filter) {
            return $filter->routeList->pluck('route')->contains('*');
        });

        $otherFilters = $filters->diff($starFilters);

        // Process filters with route '*'
        foreach ($starFilters as $filter) {
            $ips = $filter->ipList->pluck('ip')->toArray();
            $blockCode = $filter->code ?? 403;

            if (IpUtils::checkIp($request->getClientIp(), $ips)) {
                if ($filter->list_type === 'blacklist') {
                    // Block the request for blacklisted IPs
                    abort($blockCode);
                } elseif ($filter->list_type === 'whitelist') {
                    // Allow the request for whitelisted IPs
                    return $next($request);
                }
            } else {
                if ($filter->list_type === 'whitelist') {
                    // Block the request if the IP is not in the whitelist
                    abort($blockCode);
                }
                // For blacklists, do nothing if IP doesn't match
            }
        }

        // Process other filters
        foreach ($otherFilters as $filter) {
            $routes = $filter->routeList->pluck('route')->toArray();
            $ips = $filter->ipList->pluck('ip')->toArray();
            $blockCode = $filter->code ?? 403;

            // Check if the request matches any of the routes in the filter
            if ($request->is($routes)) {
                if (IpUtils::checkIp($request->getClientIp(), $ips)) {
                    if ($filter->list_type === 'blacklist') {
                        // Block the request for blacklisted IPs
                        abort($blockCode);
                    } elseif ($filter->list_type === 'whitelist') {
                        // Allow the request for whitelisted IPs
                        return $next($request);
                    }
                } else {
                    if ($filter->list_type === 'whitelist') {
                        // Block the request if the IP is not in the whitelist
                        abort($blockCode);
                    }
                    // For blacklists, do nothing if IP doesn't match
                }
            }
            // Continue to the next filter if the route doesn't match
        }

        // Allow the request if no filters apply
        return $next($request);
    }
}
