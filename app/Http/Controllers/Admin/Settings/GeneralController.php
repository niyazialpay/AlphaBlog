<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GeneralSettingsRequest;
use App\Models\Settings\GeneralSettings;
use Illuminate\Http\Request;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use niyazialpay\MediaLibrary\MediaCollections\Exceptions\MediaCannotBeDeleted;

class GeneralController extends Controller
{
    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function save(GeneralSettingsRequest $request){
        $settings = GeneralSettings::first();
        $settings->contact_email = $request->contact_email;

        if ($request->hasFile('site_logo') && $request->file('site_logo')->isValid()) {
            $settings->addMediaFromRequest('site_logo')->toMediaCollection('site_logo');
        }

        if ($request->hasFile('site_favicon') && $request->file('site_favicon')->isValid()) {
            $settings->addMediaFromRequest('site_favicon')->toMediaCollection('site_favicon');
        }

        $settings->save();

        return redirect()->back()->with('success', __('settings.general_settings_saved'));
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function deleteLogo(){
        $settings = GeneralSettings::first();
        $settings->deleteMedia($settings->getFirstMedia('site_logo'));
        return response()->json(['success' => __('settings.logo_deleted_successfully')]);
    }

    /**
     * @throws MediaCannotBeDeleted
     */
    public function deleteFavicon(){
        $settings = GeneralSettings::first();
        $settings->deleteMedia($settings->getFirstMedia('site_favicon'));
        return response()->json(['success' => __('settings.favicon_deleted_successfully')]);
    }
}
