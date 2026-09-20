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

            return request()->inertia()
                ? back()->with('success', __('settings.seo_settings_saved'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('settings.seo_settings_saved'),
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return request()->inertia()
                ? back()->with('error', $e->getMessage())
                : response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    public function saveRobots(Request $request)
    {
        file_put_contents(public_path('robots.txt'), $request->post('robots_txt'));

        return request()->inertia()
                ? back()->with('success', __('settings.robots_txt_saved'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('settings.robots_txt_saved'),
                ]);
    }

    public function saveLlms(Request $request): JsonResponse
    {
        // first() satir yoksa null doner ve ->update() fatal verirdi (temiz kurulum).
        $settings = GeneralSettings::first() ?? new GeneralSettings;
        $settings->fill([
            'llms_txt_intro' => $request->post('llms_txt_intro'),
            'llms_txt_instructions' => $request->post('llms_txt_instructions'),
        ])->save();

        Cache::forget(config('cache.prefix').'general_settings');
        Cache::forget('llms_txt_content');
        Cache::forget('llms_full_txt_content');

        return request()->inertia()
                ? back()->with('success', __('settings.llms_txt_saved'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('settings.llms_txt_saved'),
                ]);
    }

    public function clearLlmsCache(): JsonResponse
    {
        Cache::forget('llms_txt_content');
        Cache::forget('llms_full_txt_content');

        return request()->inertia()
                ? back()->with('success', __('settings.llms_txt_cache_cleared'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('settings.llms_txt_cache_cleared'),
                ]);
    }

    public function saveGoogleIndexing(Request $request): JsonResponse
    {
        $updateData = [
            'google_indexing_enabled' => $request->boolean('google_indexing_enabled'),
            'google_indexing_daily_limit' => (int) $request->post('google_indexing_daily_limit', 200),
            'google_indexing_site_url' => $request->post('google_indexing_site_url') ?: null,
        ];

        $settings = GeneralSettings::first() ?? new GeneralSettings;
        $settings->fill($updateData)->save();
        Cache::forget(config('cache.prefix').'general_settings');

        return request()->inertia()
                ? back()->with('success', __('settings.google_indexing_saved'))
                : response()->json([
                    'status' => 'success',
                    'message' => __('settings.google_indexing_saved'),
                ]);
    }
}
