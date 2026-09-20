<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\AdvertiseSettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdvertiseSettingsController extends Controller
{
    public function save(Request $request)
    {
        try {
            DB::beginTransaction();
            $settings = AdvertiseSettings::first();
            $settings->fill($request->except('_token'));
            $settings->save();
            Cache::forget(config('cache.prefix').'advertise_settings');
            DB::commit();

            return request()->inertia()
                ? back()->with('success', __('settings.advertise_save_success'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('settings.advertise_save_success'),
                ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return request()->inertia()
                ? back()->with('error', $e->getMessage())
                : response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
        }
    }
}
