<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\NotificationSettingsRequest;
use App\Models\AdminOneSignal;
use App\Models\OneSignal;
use Illuminate\Support\Facades\Cache;

class NotificationsController extends Controller
{
    public function save(NotificationSettingsRequest $request)
    {
        $admin_onesignal = AdminOneSignal::first();
        if (! $admin_onesignal) {
            $admin_onesignal = new AdminOneSignal();
        }
        $admin_onesignal->onesignal = $request->post('onesignal');
        $admin_onesignal->save();
        $onesignal = OneSignal::first();
        if (! $onesignal) {
            $onesignal = new OneSignal();
        }
        $onesignal->app_id = $request->post('app_id');
        $onesignal->auth_key = $request->post('auth_key');
        $onesignal->safari_web_id = $request->post('safari_web_id');
        $onesignal->save();
        Cache::forget(config('cache.prefix').'onesignal_settings');

        return redirect()->back()->with('success', __('settings.notifications_success'));
    }
}
