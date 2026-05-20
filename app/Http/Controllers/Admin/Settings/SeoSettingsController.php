<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeoSettingsController extends Controller
{
    public function save(Request $request, SeoSettings $seo_settings)
    {
        try {
            DB::beginTransaction();
            foreach (Languages::all() as $language) {
                $seo = $seo_settings->where('language', $language->code)->first();
                $seo->site_name = $request->post('site_name_'.$language->code);
                $seo->title = $request->post('site_title_'.$language->code);
                $seo->description = $request->post('site_description_'.$language->code);
                $seo->keywords = $request->post('site_keywords_'.$language->code);
                $seo->author = $request->post('site_author_'.$language->code);
                $seo->robots = $request->post('robots_'.$language->code);
                Cache::forget(config('cache.prefix').'seo_settings_'.$language->code);
                $seo->save();
            }
            DB::commit();

            return response()->json(['status' => 'success', 'message' => __('settings.seo_settings_saved')]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function saveRobots(Request $request)
    {
        file_put_contents(public_path('robots.txt'), $request->post('robots_txt'));

        return response()->json(['status' => 'success', 'message' => __('settings.robots_txt_saved')]);
    }

    public function saveLlms(Request $request): JsonResponse
    {
        GeneralSettings::first()->update([
            'llms_txt_intro' => $request->post('llms_txt_intro'),
            'llms_txt_instructions' => $request->post('llms_txt_instructions'),
        ]);

        Cache::forget(config('cache.prefix').'general_settings');
        Cache::forget('llms_txt_content');
        Cache::forget('llms_full_txt_content');

        return response()->json(['status' => 'success', 'message' => __('settings.llms_txt_saved')]);
    }

    public function clearLlmsCache(): JsonResponse
    {
        Cache::forget('llms_txt_content');
        Cache::forget('llms_full_txt_content');

        return response()->json(['status' => 'success', 'message' => __('settings.llms_txt_cache_cleared')]);
    }

    public function saveGoogleIndexing(Request $request): JsonResponse
    {
        $credentialsJson = null;

        if ($request->hasFile('google_indexing_credentials_file')) {
            $credentialsJson = file_get_contents($request->file('google_indexing_credentials_file')->getRealPath());
        } elseif ($request->filled('google_indexing_credentials')) {
            $credentialsJson = $request->post('google_indexing_credentials');
        }

        if ($credentialsJson) {
            $decoded = json_decode($credentialsJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['status' => 'error', 'message' => __('settings.google_indexing_invalid_json')]);
            }

            if (empty($decoded['client_email']) || empty($decoded['private_key'])) {
                return response()->json(['status' => 'error', 'message' => __('settings.google_indexing_invalid_credentials')]);
            }
        }

        $updateData = [
            'google_indexing_enabled' => $request->boolean('google_indexing_enabled'),
            'google_indexing_daily_limit' => (int) $request->post('google_indexing_daily_limit', 200),
            'google_indexing_site_url' => $request->post('google_indexing_site_url') ?: null,
        ];

        if ($credentialsJson) {
            $updateData['google_indexing_credentials'] = $credentialsJson;
        }

        GeneralSettings::first()->update($updateData);
        Cache::forget(config('cache.prefix').'general_settings');

        return response()->json(['status' => 'success', 'message' => __('settings.google_indexing_saved')]);
    }
}
