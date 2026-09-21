<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ThemeSettingsRequest;
use App\Models\Themes;
use Exception;
use File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;
use ZipArchive;

class ThemesSettingsController extends Controller
{
    public function upload(ThemeSettingsRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $zip = new ZipArchive;
            $status = $zip->open($request->file('theme')->getRealPath());
            if ($status !== true) {
                // Bu yol commit GORMUYOR: rollback olmadan transaction acik kalir
                // ve baglanti istek boyunca kilit tutar.
                DB::rollBack();

                return back()->with('error', __('themes.theme_upload_error'));
            } else {
                $zip->extractTo(base_path());
                $zip->close();
            }
            $json = file_get_contents(base_path('theme.json'));
            $theme = new Themes;
            $theme->name = json_decode($json)->name;
            $theme->is_default = false;
            $theme->save();
            unlink(base_path('theme.json'));
            DB::commit();

            return back()->with('success', __('themes.theme_save_success'));
        } catch (Throwable $e) {
            DB::rollBack();

            return back()->with('error', __('themes.theme_save_error'));
        }
    }

    public function delete(Request $request, Themes $themes, File $file): RedirectResponse
    {
        try {
            $theme = $themes::where('id', $request->post('id'))->first();

            /*
             * `first()` null donebiliyor; `$theme->is_default` erisimi `Error`
             * firlatir ve `catch (Exception)` onu YAKALAMAZ -> 500.
             */
            if (! $theme) {
                return back()->with('error', __('themes.delete_error'));
            }

            if ($theme->is_default) {
                return back()->with('error', __('themes.theme_has_default'));
            }
            $theme_public_path = public_path('theme/'.$theme->name);
            $theme_resource_path = resource_path('views/themes/'.$theme->name);
            if ($file->exists($theme_public_path)) {
                $file->deleteDirectory($theme_public_path);
            }
            if ($file->exists($theme_resource_path)) {
                $file->deleteDirectory($theme_resource_path);
            }
            $theme->delete();

            return back()->with('success', __('themes.delete_success'));
        } catch (Throwable $e) {
            return back()->with('error', __('themes.delete_error'));
        }
    }

    public function makeDefault(Themes $theme): RedirectResponse
    {
        try {
            Themes::where('is_default', true)->update(['is_default' => false]);
            $theme->is_default = true;
            $theme->save();
            Cache::forget(config('cache.prefix').'theme');

            return back()->with('success', __('themes.theme_default_success'));
        } catch (Throwable $e) {
            return back()->with('error', __('themes.theme_save_error').' '.$e->getMessage());
        }
    }
}
