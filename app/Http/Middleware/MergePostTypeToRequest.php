<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergePostTypeToRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->route()->parameter('type') == 'pages'){
            $request->merge([
                'post_type' => 'page',
            ]);
        }
        elseif($request->route()->parameter('type') == 'blogs'){
            $request->merge([
                'post_type' => 'post',
            ]);
        }
        else{
            abort(404);
        }

        return $next($request);
    }
}
