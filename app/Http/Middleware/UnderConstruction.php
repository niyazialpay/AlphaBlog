<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class UnderConstruction
{
    protected array $except = [
        'corbado/webhook',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.debug')) {
            $filter = \App\Models\IPFilter\IPFilter::where('is_active', true)->where('list_type', 'whitelist')->get();
            if ($filter->count() > 0) {
                $status = false;
                foreach ($filter as $filter_item) {
                    if (IpUtils::checkIp($request->getClientIp(), $filter_item->ip_range)) {
                        $status = true;
                        break;
                    }
                }
                if (! $status) {
                    return response(view('under-construction'), 503);
                }
            }
        }

        return $next($request);
    }
}
