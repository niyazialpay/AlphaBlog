<?php

namespace App\Providers;

use App\Models\Languages;
use App\Models\Settings\AdvertiseSettings;
use App\Models\Settings\AnalyticsSettings;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use App\Models\SocialNetworks;
use App\Models\Themes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use View;

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
        App::singleton('general_settings', function () {
            return GeneralSettings::first();
        });
        App::singleton('languages', function () {
            return Languages::where('is_active', true)->hint('code_1_unique_1')->get();
        });
        App::singleton('default_language', function () {
            return Languages::where('is_default', true)->first();
        });
        App::singleton('theme', function () {
            return Themes::where('is_default', true)->first();
        });
        App::singleton('ad_settings', function () {
            return AdvertiseSettings::first();
        });
        App::singleton('analytic_settings', function () {
            return AnalyticsSettings::first();
        });
        App::singleton('social_settings', function () {
            return SocialNetworks::where('type', 'website')->first();
        });

        View::share('languages', app('languages'));
        View::share('default_language', app('default_language'));
        View::share('theme', app('theme'));
        View::share('general_settings', app('general_settings'));
        View::share('ad_settings', app('ad_settings'));
        View::share('analytic_settings', app('analytic_settings'));
        View::share('social_settings', app('social_settings'));
    }
}
