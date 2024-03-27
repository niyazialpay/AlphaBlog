<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\NotificationSettingsRequest;
use App\Models\AdminOneSignal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationsController extends Controller
{
    public function save(NotificationSettingsRequest $request)
    {
        $admin_onesignal = AdminOneSignal::first();
        if(!$admin_onesignal) {
            $admin_onesignal = new AdminOneSignal();
        }
        $admin_onesignal->onesignal = $request->post('onesignal');
        $admin_onesignal->save();
        Cache::forget(config('cache.prefix').'admin_notification_settings');
        return redirect()->back()->with('success', __('settings.notifications_success'));
    }
}
