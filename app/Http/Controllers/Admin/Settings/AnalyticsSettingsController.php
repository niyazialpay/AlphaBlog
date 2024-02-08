<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\AnalyticsSettings;
use Illuminate\Http\Request;

class AnalyticsSettingsController extends Controller
{
    public function save(Request $request){
        $settings = AnalyticsSettings::first();
        $settings->fill($request->except('_token'));
        $settings->save();
        return response()->json([
            'status' => 'success',
            'message' => __('settings.analytics_save_success')
        ], 200);
    }
}
