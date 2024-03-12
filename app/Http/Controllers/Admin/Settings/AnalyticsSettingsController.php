<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\AnalyticsSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsSettingsController extends Controller
{
    public function save(Request $request){
        $settings = AnalyticsSettings::first();
        $settings->fill($request->except('_token'));
        $settings->save();
        Cache::forget(config('cache.prefix').'analytic_settings');
        return response()->json([
            'status' => 'success',
            'message' => __('settings.analytics_save_success')
        ], 200);
    }
}
