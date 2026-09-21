<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Models\Settings\GeneralSettings;
use App\Models\Settings\SeoSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeoSettingsController extends Controller
{
    public function save(Request $request, SeoSettings $seo_settings): RedirectResponse
    {
        try {
            DB::beginTransaction();

            /*
             * DIL KAPSAMI.
             *
             * Eski Blade formu TUM dilleri tek gonderimde, dil ekli adlarla
             * yolluyordu (`site_name_tr`, `site_name_en`, ...). Vue formu ise
             * sekme basina YALNIZCA aktif dilin duz alanlarini yolluyor
             * (`site_name`, `title`, ... + `language`).
             *
             * Ikisi ayirt edilmezse Vue gonderimi her dil icin dil ekli anahtari
             * arar, hepsini null bulur ve TUM dillerin SEO satirlarini NULL'lar.
             * Kolonlar nullable oldugu icin DB reddetmez, commit gecer, cache
             * temizlenir ve kullanici "kaydedildi" mesaji gorur. Sessiz ve geri
             * alinamaz veri kaybi.
             */
            $scoped = $request->filled('language');

            $languages = $scoped
                ? Languages::where('code', $request->post('language'))->get()
                : Languages::all();

            foreach ($languages as $language) {
                $suffix = $scoped ? '' : '_'.$language->code;

                // Bir dil icin satir yoksa `first()` null doner ve sonraki
                // ozellik atamasi `Error` firlatir (Exception DEGIL).
                $seo = $seo_settings->newQuery()->firstOrNew(['language' => $language->code]);

                $seo->site_name = $request->post($scoped ? 'site_name' : 'site_name'.$suffix);
                $seo->title = $request->post($scoped ? 'title' : 'site_title'.$suffix);
                $seo->description = $request->post($scoped ? 'description' : 'site_description'.$suffix);
                $seo->keywords = $request->post($scoped ? 'keywords' : 'site_keywords'.$suffix);
                $seo->author = $request->post($scoped ? 'author' : 'site_author'.$suffix);
                $seo->robots = $request->post($scoped ? 'robots' : 'robots'.$suffix);

                Cache::forget(config('cache.prefix').'seo_settings_'.$language->code);
                $seo->save();
            }

            DB::commit();

            return back()->with('success', __('settings.seo_settings_saved'));
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function saveRobots(Request $request): RedirectResponse
    {
        file_put_contents(public_path('robots.txt'), $request->post('robots_txt'));

        return back()->with('success', __('settings.robots_txt_saved'));
    }

    public function saveLlms(Request $request): RedirectResponse
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

        return back()->with('success', __('settings.llms_txt_saved'));
    }

    public function clearLlmsCache(): RedirectResponse
    {
        Cache::forget('llms_txt_content');
        Cache::forget('llms_full_txt_content');

        return back()->with('success', __('settings.llms_txt_cache_cleared'));
    }

    public function saveGoogleIndexing(Request $request): RedirectResponse
    {
        $updateData = [
            'google_indexing_enabled' => $request->boolean('google_indexing_enabled'),
            'google_indexing_daily_limit' => (int) $request->post('google_indexing_daily_limit', 200),
            'google_indexing_site_url' => $request->post('google_indexing_site_url') ?: null,
        ];

        $settings = GeneralSettings::first() ?? new GeneralSettings;
        $settings->fill($updateData)->save();
        Cache::forget(config('cache.prefix').'general_settings');

        return back()->with('success', __('settings.google_indexing_saved'));
    }
}
