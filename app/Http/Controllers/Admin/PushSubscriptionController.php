<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Support\Notifications\NotificationEvents;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Tarayici push abonelikleri ve kullanici bildirim tercihleri.
 *
 * ABONELIK UCLARI VERI UCUDUR (R1): tarayicinin `PushManager` nesnesinden gelen
 * degerleri kaydeder/siler ve JSON doner; Vue tarafi `axios` ile cagirir. Bir
 * sayfa gecisi degil, arka plandaki bir durum degisikligi.
 *
 * TERCIH KAYDI ise FORM eylemidir: profil ekranindan gonderilir ve yonlendirir.
 */
class PushSubscriptionController extends Controller
{
    /**
     * Abonelik kaydi (upsert).
     *
     * Ayni endpoint tekrar gonderilebilir: tarayici izni yenilendiginde ya da
     * sayfa her acildiginda istemci mevcut aboneligi bildirir. `endpoint_hash`
     * uzerinden upsert yapilir, mukerrer satir olusmaz.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'max:32'],
        ]);

        $endpoint = $data['endpoint'];

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => PushSubscription::hashFor($endpoint)],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $endpoint,
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aesgcm',
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'last_used_at' => now(),
            ]
        );

        return response()->json(['status' => true]);
    }

    /**
     * Abonelik silme.
     *
     * Yalniz KENDI aboneligini silebilir; endpoint tahmin edilebilir bir deger
     * olmasa da sahiplik kontrolu atlanmaz.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('endpoint_hash', PushSubscription::hashFor($data['endpoint']))
            ->delete();

        return response()->json(['status' => true]);
    }

    /**
     * Bildirim tercihleri.
     *
     * YETKI SUNUCUDA YENIDEN DEGERLENDIRILIR: istemciden gelen liste degil,
     * `NotificationEvents::forUser()` belirleyici. Boylece bir kullanici
     * gormedigi bir olayi POST ederek kendine acamaz.
     */
    public function preferences(Request $request): RedirectResponse
    {
        $user = $request->user();
        $allowed = NotificationEvents::forUser($user);

        $request->validate([
            'preferences' => ['present', 'array'],
            'preferences.*.database' => ['boolean'],
            'preferences.*.push' => ['boolean'],
        ]);

        $submitted = (array) $request->input('preferences', []);

        try {
            DB::beginTransaction();

            foreach (array_keys($allowed) as $event) {
                $values = $submitted[$event] ?? null;

                if (! is_array($values)) {
                    continue;
                }

                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'event' => $event],
                    [
                        'database' => (bool) ($values['database'] ?? false),
                        'push' => (bool) ($values['push'] ?? false),
                    ]
                );
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            return back()->with('error', __('notifications.preferences_error'));
        }

        return back()->with('success', __('notifications.preferences_saved'));
    }
}
