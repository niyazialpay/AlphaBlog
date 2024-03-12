<?php

namespace App\Http\Middleware;

use App\Action\LanguageAction;
use App\Models\ContactPage;
use App\Models\Settings\SeoSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
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

        if(Cache::has(config('cache.prefix').'seo_settings_'.session('language'))){
             $seo_settings = Cache::get(config('cache.prefix').'seo_settings_'.session('language'));
        }
        else{
            $seo_settings = Cache::rememberForever(
                config('cache.prefix').'seo_settings_'.session('language'), function(){
                return SeoSettings::where('language', session('language'))->first();
            });
        }

        App::singleton('seo_settings', function () use($seo_settings) {
            return $seo_settings;
        });

        View::share('seo_settings', $seo_settings);

        if(Cache::has(config('cache.prefix').'contact_page_'.session('language'))){
            $contact_page = Cache::get(config('cache.prefix').'contact_page_'.session('language'));
        }
        else{
            $contact_page = Cache::rememberForever(
                config('cache.prefix').'contact_page_'.session('language'), function(){
                return ContactPage::where('language', session('language'))->first();
            });
        }
        App::singleton('contact_page', function () use($contact_page) {
            return $contact_page;
        });
        View::share('contact_page', $contact_page);

        return $next($request);
    }
}
