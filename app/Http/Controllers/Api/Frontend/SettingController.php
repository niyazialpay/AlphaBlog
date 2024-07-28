<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;

class SettingController extends Controller
{
    public function generalSettings($language)
    {
        $general_settings = GeneralSettings::with('media')->first();
        return response()->json([
            'seo_settings' => SeoSettings::where('language', $language)->first(),
            'site_logo_light' => $general_settings->getFirstMediaUrl('site_logo_light'),
            'site_logo_dark' => $general_settings->getFirstMediaUrl('site_logo_dark')
        ], '200', [], JSON_PRETTY_PRINT);
    }
}
