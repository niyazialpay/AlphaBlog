<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ThemeSettingsRequest;
use App\Models\Themes;
use Exception;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ThemesSettingsController extends Controller
{
    public function upload(ThemeSettingsRequest $request)
    {
        try {
            DB::beginTransaction();
            $zip = new ZipArchive;
            $status = $zip->open($request->file('theme')->getRealPath());
            if ($status !== true) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('themes.theme_upload_error'),
                    'error' => $status,
                ]);
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

            return response()->json([
                'status' => 'success',
                'message' => __('themes.theme_save_success'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => __('themes.theme_save_error'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function delete(Request $request, Themes $themes, File $file)
    {
        try {
            $theme = $themes::where('id', $request->post('id'))->first();
            if ($theme->is_default) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('themes.theme_has_default'),
                ]);
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

            return response()->json([
                'status' => 'success',
                'message' => __('themes.delete_success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('themes.delete_error'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function makeDefault(Themes $theme)
    {
        try {
            Themes::where('is_default', true)->update(['is_default' => false]);
            $theme->is_default = true;
            $theme->save();
            Cache::forget(config('cache.prefix').'theme');

            return back()->with('success', __('themes.theme_default_success'));
        } catch (Exception $e) {
            return back()->with('error', __('themes.theme_save_error').' '.$e->getMessage());
        }
    }
}
