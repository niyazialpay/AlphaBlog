<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Models\Settings\SeoSettings;
use Illuminate\Http\Request;

class SeoSettingsController extends Controller
{
    public function save(Request $request, SeoSettings $seo_settings)
    {
        foreach(Languages::all() as $language) {
            $seo = $seo_settings->where('language', $language->code)->first();
            $seo->title = $request->post('site_title_'.$language->code);
            $seo->description = $request->post('site_description_'.$language->code);
            $seo->keywords = $request->post('site_keywords_'.$language->code);
            $seo->author = $request->post('site_author_'.$language->code);
            $seo->robots = $request->post('robots_'.$language->code);
            $seo->save();
        }
        return response()->json(['status' => 'success', 'message' => __('settings.seo_settings_saved')]);
    }

    public function saveRobots(Request $request)
    {
        file_put_contents(public_path('robots.txt'), $request->post('robots_txt'));
        return response()->json(['status' => 'success', 'message' => __('settings.robots_txt_saved')]);
    }
}
