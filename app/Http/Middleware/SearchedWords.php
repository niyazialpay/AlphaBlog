<?php

namespace App\Http\Middleware;

use App\Models\Search;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SearchedWords
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('searchedWordsCount', Search::where('checked', false)->count());
        return $next($request);
    }
}
