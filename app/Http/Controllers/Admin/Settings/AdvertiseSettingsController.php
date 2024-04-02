<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\AdvertiseSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdvertiseSettingsController extends Controller
{
    public function save(Request $request)
    {
        $settings = AdvertiseSettings::first();
        $settings->fill($request->except('_token'));
        $settings->save();
        Cache::forget(config('cache.prefix').'advertise_settings');

        return response()->json([
            'status' => 'success',
            'message' => __('settings.advertise_save_success'),
        ], 200);
    }
}
