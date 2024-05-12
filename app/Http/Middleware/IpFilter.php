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
        $blacklisted_ips = [];
        $whitelisted_ips = [];
        $blacklisted_route_list = [];
        $whitelisted_route_list = [];
        foreach ($filter as $filter_item) {
            //print_r($filter_item->ipList->pluck('ip')->toArray());
            if($request->is($filter_item->routeList->pluck('route')->toArray())){
                /*echo "Route: ".$request->path()."\n";
                echo "Client IP: ".$request->getClientIp()."\n";
                echo "List Type: ".$filter_item->list_type."\n";
                if($filter_item->list_type == 'blacklist')
                    echo "Blacklisted ";
                else
                    echo "Whitelisted ";
                echo "IP List: ".implode(", ", $filter_item->ipList->pluck('ip')->toArray())."\n\n\n";*/


                if($filter_item->list_type == 'blacklist'){
                    $blacklisted_ips = array_merge($blacklisted_ips, $filter_item->ipList->pluck('ip')->toArray());
                    $blacklisted_route_list = array_merge($blacklisted_route_list, $filter_item->routeList->pluck('route')->toArray());
                }
                else{
                    $whitelisted_ips = array_merge($whitelisted_ips, $filter_item->ipList->pluck('ip')->toArray());
                    $whitelisted_route_list = array_merge($whitelisted_route_list, $filter_item->routeList->pluck('route')->toArray());
                }

            }
            /*if (IpUtils::checkIp($request->getClientIp(), $filter_item->ipList->pluck('ip')->toArray())) {
                if ($request->is($filter_item->routeList->pluck('route')->toArray())) {
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
            }
            else {
                if ($request->is($filter_item->routeList->pluck('route')->toArray())) {
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
            }*/
        }

        if(count($blacklisted_ips) > 0){
            if (IpUtils::checkIp($request->getClientIp(), $blacklisted_ips) && $request->is(implode(',', $blacklisted_route_list))) {
                abort(404);
            }
        }

        if(count($whitelisted_ips) > 0){
            if (IpUtils::checkIp($request->getClientIp(), $whitelisted_ips) && $request->is(implode(',', $whitelisted_route_list))) {
                abort(404);
            }
            else{
                return $next($request);
            }
        }
        return $next($request);
        /*print_r($blacklisted_ips);
        if ($status) {
            return $next($request);
        }
        //abort(404);
        return $next($request);*/
    }
}
