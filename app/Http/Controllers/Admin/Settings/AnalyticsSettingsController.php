<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\AnalyticsSettings;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsSettingsController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $settings = AnalyticsSettings::first();
            $settings->fill($request->except('_token'));
            $settings->save();
            Cache::forget(config('cache.prefix').'analytic_settings');
            DB::commit();

            return back()->with('success', __('settings.analytics_save_success'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
}
