<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Actions\SocialNetworkSaveAction;
use App\Http\Controllers\Controller;
use App\Models\Settings\SocialSettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SocialSettingsController extends Controller
{
    public function save(Request $request)
    {
        try {
            if (SocialNetworkSaveAction::execute($request, 'website')) {
                Cache::forget(config('cache.prefix').'social_networks');

                return request()->inertia()
                    ? back()->with('success', __('profile.save_success'))
                    : response()->json([
                        'status' => 'success',
                        'message' => __('profile.save_success'),
                    ], 200);
            } else {
                return request()->inertia()
                    ? back()->with('error', __('profile.save_error'))
                    : response()->json([
                        'status' => 'error',
                        'message' => __('profile.save_error'),
                    ], 422);
            }
        } catch (Exception $e) {
            return request()->inertia()
                ? back()->with('error', $e->getMessage())
                : response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
        }
    }

    /**
     * Istekten gelen sosyal ag listesini gecerli anahtarlara indirger.
     *
     * Eski jQuery formu `social_networks_header[]` dizisi gonderiyordu; Inertia
     * formu da dizi gonderir. Skaler/gecersiz her deger elenir.
     *
     * @return list<string>
     */
    private static function networkList(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        $allowed = array_keys(social_list());

        return array_values(array_unique(array_filter(
            $value,
            fn ($item) => is_string($item) && in_array($item, $allowed, true),
        )));
    }

    public function saveHeader(Request $request)
    {
        try {
            DB::beginTransaction();
            /*
             * Bu iki kolon HANGI aglarin gosterilecegini tutan bir liste.
             * Ham istek degeri dogrudan yazilirsa (ornegin bir checkbox'tan gelen
             * "1"/"0") kolona skaler dusuyor ve Blade temalarinda
             * `in_array(..., json_decode($show, true))` TypeError firlatiyor.
             * Daima gecerli `social_list()` anahtarlarindan olusan bir listeye indirgenir.
             */
            $header = self::networkList($request->input('social_networks_header'));
            $footer = self::networkList($request->input('social_networks_footer'));

            $socialSettings = SocialSettings::first();
            if ($socialSettings) {
                $socialSettings->social_networks_header = $header;
                $socialSettings->social_networks_footer = $footer;
                $socialSettings->save();
            } else {
                SocialSettings::create([
                    'social_networks_header' => $header,
                    'social_networks_footer' => $footer,
                ]);
            }
            Cache::forget(config('cache.prefix').'social_settings');
            DB::commit();

            return request()->inertia()
                ? back()->with('success', __('profile.save_success'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('profile.save_success'),
                ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return request()->inertia()
                ? back()->with('error', __('profile.save_error'))
                : response()->json([
                    'status' => 'error',
                    'message' => __('profile.save_error'),
                    'error' => $e->getMessage(),
                ], 422);
        }
    }
}
