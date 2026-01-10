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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GlobalVariableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // APP_KEY yoksa zaten devam etme
        if (!config('app.key')) {
            return;
        }

        /**
         * Yeni kurulum / migrate sırasında provider'ın DB'ye dalıp patlamasını engelle.
         * migrate/seed/config:cache vs. konsol komutlarında View share vs. gereksiz.
         */
        if (app()->runningInConsole()) {
            return;
        }

        /**
         * Tablolar yokken sorgu atma.
         * (İstersen burada hangi tablolar şartsa onları listeleyebilirsin.)
         */
        $requiredTables = [
            'general_settings',
            'languages',
            'themes',
            'advertise_settings',
            'analytics_settings',
            'social_networks',
            'social_settings',
        ];

        // DB bağlantısı/tablolar hazır değilse sessizce çık
        try {
            foreach ($requiredTables as $table) {
                if (!Schema::hasTable($table)) {
                    return;
                }
            }
        } catch (\Throwable $e) {
            // DB daha ayağa kalkmamış olabilir (ilk kurulum)
            return;
        }

        $prefix = (string) config('cache.prefix');

        // GENERAL SETTINGS
        if (Cache::has($prefix.'general_settings')) {
            $general_settings = Cache::get($prefix.'general_settings');
        } else {
            $general_settings = Cache::rememberForever($prefix.'general_settings', function () {
                return GeneralSettings::first();
            });
        }
        App::singleton('general_settings', fn () => $general_settings);

        // LANGUAGES
        if (Cache::has($prefix.'languages')) {
            $languages = Cache::get($prefix.'languages');
        } else {
            $languages = Cache::rememberForever($prefix.'languages', function () {
                return Languages::where('is_active', true)->get();
            });
        }
        App::singleton('languages', fn () => $languages);

        // DEFAULT LANGUAGE
        if (Cache::has($prefix.'default_language')) {
            $default_language = Cache::get($prefix.'default_language');
        } else {
            $default_language = Cache::rememberForever($prefix.'default_language', function () {
                return Languages::where('is_default', true)->first();
            });
        }
        App::singleton('default_language', fn () => $default_language);

        // THEME
        if (Cache::has($prefix.'theme')) {
            $theme = Cache::get($prefix.'theme');
        } else {
            $theme = Cache::rememberForever($prefix.'theme', function () {
                return Themes::where('is_default', true)->first();
            });
        }
        App::singleton('theme', fn () => $theme);

        // ADVERTISE SETTINGS
        if (Cache::has($prefix.'advertise_settings')) {
            $ad_settings = Cache::get($prefix.'advertise_settings');
        } else {
            $ad_settings = Cache::rememberForever($prefix.'advertise_settings', function () {
                return AdvertiseSettings::first();
            });
        }
        App::singleton('ad_settings', fn () => $ad_settings);

        // ANALYTICS SETTINGS
        if (Cache::has($prefix.'analytic_settings')) {
            $analytic_settings = Cache::get($prefix.'analytic_settings');
        } else {
            $analytic_settings = Cache::rememberForever($prefix.'analytic_settings', function () {
                return AnalyticsSettings::first();
            });
        }
        App::singleton('analytic_settings', fn () => $analytic_settings);

        // SOCIAL NETWORKS
        if (Cache::has($prefix.'social_networks')) {
            $social_networks = Cache::get($prefix.'social_networks');
        } else {
            $social_networks = Cache::rememberForever($prefix.'social_networks', function () {
                return SocialNetworks::where('type', 'website')->first();
            });
        }
        App::singleton('social_networks', fn () => $social_networks);

        // SOCIAL SETTINGS
        if (Cache::has($prefix.'social_settings')) {
            $social_settings = Cache::get($prefix.'social_settings');
        } else {
            $social_settings = Cache::rememberForever($prefix.'social_settings', function () {
                return SocialSettings::first();
            });
        }
        App::singleton('social_settings', fn () => $social_settings);

        // View share
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
