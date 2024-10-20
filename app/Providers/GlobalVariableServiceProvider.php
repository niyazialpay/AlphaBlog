<?php

namespace App\Providers;

use App\Models\Languages;
use App\Models\Settings\AdvertiseSettings;
use App\Models\Settings\AnalyticsSettings;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SocialSettings;
use App\Models\SocialNetworks;
use App\Models\Themes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GlobalVariableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (config('app.key')) {
            if (Cache::has(config('cache.prefix').'general_settings')) {
                $general_settings = Cache::get(config('cache.prefix').'general_settings');
            } else {
                $general_settings = Cache::rememberForever(config('cache.prefix').'general_settings', function () {
                    return GeneralSettings::first();
                });
            }
            App::singleton('general_settings', function () use ($general_settings) {
                return $general_settings;
            });

            if (Cache::has(config('cache.prefix').'languages')) {
                $languages = Cache::get(config('cache.prefix').'languages');
            } else {
                $languages = Cache::rememberForever(config('cache.prefix').'languages', function () {
                    return Languages::where('is_active', true)->get();
                });
            }
            App::singleton('languages', function () use ($languages) {
                return $languages;
            });

            if (Cache::has(config('cache.prefix').'default_language')) {
                $default_language = Cache::get(config('cache.prefix').'default_language');
            } else {
                $default_language = Cache::rememberForever(config('cache.prefix').'default_language', function () {
                    return Languages::where('is_default', true)->first();
                });
            }
            App::singleton('default_language', function () use ($default_language) {
                return $default_language;
            });

            if (Cache::has(config('cache.prefix').'theme')) {
                $theme = Cache::get(config('cache.prefix').'theme');
            } else {
                $theme = Cache::rememberForever(config('cache.prefix').'theme', function () {
                    return Themes::where('is_default', true)->first();
                });
            }
            App::singleton('theme', function () use ($theme) {
                return $theme;
            });

            if (Cache::has(config('cache.prefix').'ad_settings')) {
                $ad_settings = Cache::get(config('cache.prefix').'ad_settings');
            } else {
                $ad_settings = Cache::rememberForever(config('cache.prefix').'ad_settings', function () {
                    return AdvertiseSettings::first();
                });
            }
            App::singleton('ad_settings', function () use ($ad_settings) {
                return $ad_settings;
            });

            if (Cache::has(config('cache.prefix').'analytic_settings')) {
                $analytic_settings = Cache::get(config('cache.prefix').'analytic_settings');
            } else {
                $analytic_settings = Cache::rememberForever(config('cache.prefix').'analytic_settings', function () {
                    return AnalyticsSettings::first();
                });
            }
            App::singleton('analytic_settings', function () use ($analytic_settings) {
                return $analytic_settings;
            });

            if (Cache::has(config('cache.prefix').'social_networks')) {
                $social_networks = Cache::get(config('cache.prefix').'social_networks');
            } else {
                $social_networks = Cache::rememberForever(config('cache.prefix').'social_networks', function () {
                    return SocialNetworks::where('type', 'website')->first();
                });
            }
            App::singleton('social_networks', function () use ($social_networks) {
                return $social_networks;
            });

            if (Cache::has(config('cache.prefix').'social_settings')) {
                $social_settings = Cache::get(config('cache.prefix').'social_settings');
            } else {
                $social_settings = Cache::rememberForever(config('cache.prefix').'social_settings', function () {
                    return SocialSettings::first();
                });
            }
            App::singleton('social_settings', function () use ($social_settings) {
                return $social_settings;
            });

            View::share('general_settings', $general_settings);
            View::share('languages', $languages);
            View::share('default_language', $default_language);
            View::share('theme', $theme);
            View::share('ad_settings', $ad_settings);
            View::share('analytic_settings', $analytic_settings);
            View::share('social_networks', $social_networks);
            View::share('social_settings', $social_settings);
        }
    }
}
