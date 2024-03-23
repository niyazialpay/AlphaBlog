<?php

namespace App\Http\Middleware;

use App\Models\Post\Comments;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class NewCommentsCount
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('newCommentsCount', Comments::where('is_approved', false)->count());
        return $next($request);
    }
}
