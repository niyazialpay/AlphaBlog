<?php

namespace App\Http\Controllers\Admin\Settings;

use Throwable;
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
                $admin_onesignal = new AdminOneSignal;
            }
            /*
             * "BOS BIRAK, KORUNUR" semantigi.
             *
             * Panel gizli degerleri MASKELIYOR: `app_id`/`auth_key` prop olarak
             * hic gonderilmiyor (yalnizca `has_app_id`/`has_auth_key` bayraklari
             * var) ve form onlari bos string olarak yolluyor. `onesignal` alani
             * ise Vue formunda hic yok.
             *
             * Kosulsuz atama bu yuzden her kaydetmede calisan kimlik bilgilerini
             * siliyordu: kullanici sadece "Safari Web ID"yi degistirmek icin
             * kaydete bassa bile OneSignal entegrasyonu oluyordu. Bos/eksik gelen
             * alan artik MEVCUT degeri korur.
             */
            if ($request->filled('onesignal')) {
                $admin_onesignal->onesignal = $request->post('onesignal');
            }

            $admin_onesignal->save();
            $onesignal = OneSignal::first();
            if (! $onesignal) {
                $onesignal = new OneSignal;
            }
            if ($request->filled('app_id')) {
                $onesignal->app_id = $request->post('app_id');
            }

            if ($request->filled('auth_key')) {
                $onesignal->auth_key = $request->post('auth_key');
            }

            $onesignal->safari_web_id = $request->post('safari_web_id');
            $onesignal->user_segmentation = $request->post('user_segmentation');
            $onesignal->save();
            Cache::forget(config('cache.prefix').'onesignal_settings');
            Cache::forget(config('cache.prefix').'admin_notification_settings');
            DB::commit();

            return redirect()->back()->with('success', __('settings.notifications_success'));
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
