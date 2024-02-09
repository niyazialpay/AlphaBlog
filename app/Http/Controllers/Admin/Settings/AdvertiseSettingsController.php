<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\AdvertiseSettings;
use Illuminate\Http\Request;

class AdvertiseSettingsController extends Controller
{
    public function save(Request $request){
        $settings = AdvertiseSettings::first();
        $settings->fill($request->except('_token'));
        $settings->save();
        return response()->json([
            'status' => 'success',
            'message' => __('settings.advertise_save_success')
        ], 200);
    }
}
