<?php

namespace App\Http\Controllers\Admin;

use App\Actions\LanguageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\LanguageRequest;
use App\Models\Languages;
use App\Models\Menu\Menu;
use App\Models\Menu\MenuItems;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use App\Models\Settings\SeoSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class LanguagesController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(Languages::where('id', GetPost($request->post('id')))->first());
    }

    public function save(LanguageRequest $request, Languages $language): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $seo_settings = new SeoSettings;
            if (! $language->id) {
                $language = new Languages;
                $default_lang = $language::where('is_default', true)->first();
                $seo_setting = $seo_settings::where('language', $default_lang->code)->first();
                $seo_settings::create([
                    'language' => $request->post('code'),
                    'title' => $seo_setting->title,
                    'robots' => $seo_setting->robots,
                    'keywords' => $seo_setting->keywords,
                    'description' => $seo_setting->description,
                    'author' => $seo_setting->author,
                ]);

            } else {
                Posts::where('language', $language->code)->update(['language' => $request->post('code')]);
                Categories::where('language', $language->code)->update(['language' => $request->post('code')]);
                Menu::where('language', $language->code)->update(['language' => $request->post('code')]);
                MenuItems::where('language', $language->code)->update(['language' => $request->post('code')]);
                $seo_settings::where('language', $language->code)->update(['language' => $request->post('code')]);
            }
            if ($request->post('is_default') == 1) {
                Languages::where('is_default', true)->update(['is_default' => false]);
            }
            $language->name = $request->post('name');
            $language->code = $request->post('code');
            $language->flag = $request->post('flag');

            $language->is_default = $request->post('is_default') == 1;
            $language->is_active = $request->post('is_active') == 1;
            Cache::forget(config('cache.prefix').'default_language');
            Cache::forget(config('cache.prefix').'languages');
            $language->save();

            $default_language = Languages::where('is_default', true)->first();
            if (! $default_language) {
                $language->is_default = true;
                $language->save();
            }

            LanguageAction::setLanguage($request);

            DB::commit();

            return back()->with('success', __('language.save_success'));
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', __('language.save_error'));
        }
    }

    public function delete(Request $request, Languages $languages): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $language = $languages::where('id', GetPost($request->post('id')))->first();

            if (! $language) {
                DB::rollBack();

                return back()->with('error', __('language.delete_error'));
            }

            if (Posts::where('language', $language->code)->count() > 0) {
                $error_message = __('language.has_posts');
            }
            if (Categories::where('language', $language->code)->count() > 0) {
                $error_message = __('language.has_categories');
            }
            if (Menu::where('language', $language->code)->count() > 0) {
                $error_message = __('language.has_menus');
            }
            if (MenuItems::where('language', $language->code)->count() > 0) {
                $error_message = __('language.has_menu_items');
            }
            if ($language->is_default) {
                $error_message = __('language.delete_default');
            }
            if (isset($error_message)) {
                DB::rollBack();

                return back()->with('error', $error_message);
            }
            SeoSettings::where('language', $language->code)->delete();
            $language->delete();
            Cache::forget(config('cache.prefix').'default_language');
            Cache::forget(config('cache.prefix').'languages');

            DB::commit();

            return back()->with('success', __('language.delete_success'));
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', __('language.delete_error'));
        }
    }
}
