<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Models\Settings\AdvertiseSettings;
use App\Models\Settings\AnalyticsSettings;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use App\Models\Settings\SocialSettings;
use App\Models\SocialNetworks;
use App\Models\Themes;

class SettingsController extends Controller
{
    public function index()
    {
        return view('panel.settings.index', [
            'seo_settings' => new SeoSettings(),
            'general_settings' => GeneralSettings::first(),
            'advertise_settings' => AdvertiseSettings::first(),
            'analytics_settings' => AnalyticsSettings::first(),
            'all_languages' => Languages::all(),
            'social_networks' => SocialNetworks::where('type', 'website')->first(),
            'robots_txt' => file_exists(public_path('robots.txt')) ? file_get_contents(public_path('robots.txt')) : null,
            'themes' => Themes::all(),
            'social_settings' => SocialSettings::first()
        ]);
    }
}
