<?php

namespace App\Http\Middleware;

use App\Action\LanguageAction;
use App\Models\Settings\SeoSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        LanguageAction::setLanguage($request);
        View::share('seo_settings', SeoSettings::where('language', session()->get('language'))->first());
        return $next($request);
    }
}
