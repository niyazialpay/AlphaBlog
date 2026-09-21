<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GeneralSettingsRequest;
use App\Models\Settings\GeneralSettings;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeneralSettingsController extends Controller
{
    public function save(GeneralSettingsRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $settings = GeneralSettings::first();
            $settings->contact_email = $request->contact_email;
            $settings->sharethis = $request->sharethis;
            $settings->homepage_featured_count = $request->homepage_featured_count ?? 5;
            $settings->homepage_recent_count = $request->homepage_recent_count ?? 45;

            if ($request->hasFile('site_logo_light') && $request->file('site_logo_light')->isValid()) {
                $settings->addMediaFromRequest('site_logo_light')->toMediaCollection('site_logo_light');
            }

            if ($request->hasFile('site_logo_dark') && $request->file('site_logo_dark')->isValid()) {
                $settings->addMediaFromRequest('site_logo_dark')->toMediaCollection('site_logo_dark');
            }

            if ($request->hasFile('site_favicon') && $request->file('site_favicon')->isValid()) {
                $settings->addMediaFromRequest('site_favicon')->toMediaCollection('site_favicon');
            }

            if ($request->hasFile('app_icon') && $request->file('app_icon')->isValid()) {
                $settings->addMediaFromRequest('app_icon')->toMediaCollection('app_icon');
            }

            $settings->save();
            Cache::forget(config('cache.prefix').'general_settings');
            DB::commit();

            return redirect()->back()->with('success', __('settings.general_settings_saved'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteLogo($type): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $settings = GeneralSettings::first();
            $settings->deleteMedia($settings->getFirstMedia('site_logo_'.$type));
            Cache::forget(config('cache.prefix').'general_settings');
            DB::commit();

            return back()->with('success', __('settings.logo_deleted_successfully'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteFavicon(): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $settings = GeneralSettings::first();
            $settings->deleteMedia($settings->getFirstMedia('site_favicon'));
            Cache::forget(config('cache.prefix').'general_settings');
            DB::commit();

            return back()->with('success', __('settings.favicon_deleted_successfully'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAppIcon(): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $settings = GeneralSettings::first();
            $settings->deleteMedia($settings->getFirstMedia('app_icon'));
            Cache::forget(config('cache.prefix').'general_settings');
            DB::commit();

            return back()->with('success', __('settings.app_icon_deleted_successfully'));
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
}
