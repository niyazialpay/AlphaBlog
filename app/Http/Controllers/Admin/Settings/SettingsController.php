<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CloudflareApiSettingsRequest;
use App\Models\Cloudflare;
use App\Models\Languages;
use App\Models\OneSignal;
use App\Models\Settings\SeoSettings;
use App\Models\Themes;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index()
    {
        return view('panel.settings.index', [
            'seo_settings' => new SeoSettings,
            'general_settings' => app('general_settings'),
            'advertise_settings' => app('ad_settings'),
            'analytics_settings' => app('analytic_settings'),
            'all_languages' => Languages::all(),
            'social_networks' => app('social_networks'),
            'robots_txt' => file_exists(public_path('robots.txt')) ?
                file_get_contents(public_path('robots.txt')) : null,
            'themes' => Themes::all(),
            'social_settings' => app('social_settings'),
            'onesignal' => Onesignal::first(),
            'cloudflare' => Cloudflare::first()
        ]);
    }

    public function updateApiSettings(CloudflareApiSettingsRequest $request): JsonResponse
    {
        $cf = Cloudflare::first();
        if(!$cf){
            $cf = new Cloudflare();
        }
        $cf->cf_email = $request->post('cf_email');
        $cf->cf_key = $request->post('cf_key');
        $cf->domain = $request->post('cf_domain');
        $cf->save();
        return response()->json([
            'status' => 'success',
            'message' => __('cloudflare.api_settings_updated')
        ]);
    }
}
