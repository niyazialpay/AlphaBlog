<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GeneralSettingsRequest;
use App\Models\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;

class GeneralSettingsController extends Controller
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function save(GeneralSettingsRequest $request){
        $settings = GeneralSettings::first();
        $settings->contact_email = $request->contact_email;
        $settings->sharethis = $request->sharethis;

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

        return redirect()->back()->with('success', __('settings.general_settings_saved'));
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function deleteLogo($type){
        $settings = GeneralSettings::first();
        $settings->deleteMedia($settings->getFirstMedia('site_logo_'.$type));
        Cache::forget(config('cache.prefix').'general_settings');
        return response()->json(['success' => __('settings.logo_deleted_successfully')]);
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function deleteFavicon(){
        $settings = GeneralSettings::first();
        $settings->deleteMedia($settings->getFirstMedia('site_favicon'));
        Cache::forget(config('cache.prefix').'general_settings');
        return response()->json(['success' => __('settings.favicon_deleted_successfully')]);
    }
    /**
     * @throws MediaCannotBeDeleted
     */
    public function deleteAppIcon(){
        $settings = GeneralSettings::first();
        $settings->deleteMedia($settings->getFirstMedia('app_icon'));
        Cache::forget(config('cache.prefix').'general_settings');
        return response()->json(['success' => __('settings.favicon_deleted_successfully')]);
    }
}
