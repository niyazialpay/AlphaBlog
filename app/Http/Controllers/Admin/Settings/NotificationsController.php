<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\NotificationSettingsRequest;
use App\Models\AdminOneSignal;
use App\Models\OneSignal;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    public function save(NotificationSettingsRequest $request)
    {
        try {
            DB::beginTransaction();
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
            Cache::forget(config('cache.prefix').'admin_notification_settings');
            DB::commit();

            return redirect()->back()->with('success', __('settings.notifications_success'));
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
