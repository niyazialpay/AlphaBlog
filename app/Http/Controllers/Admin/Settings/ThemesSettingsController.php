<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ThemeSettingsRequest;
use App\Models\Themes;
use Exception;
//use Illuminate\Filesystem\Filesystem as File;
use File;
use Illuminate\Http\Request;
use ZipArchive;

class ThemesSettingsController extends Controller
{
    public function upload(ThemeSettingsRequest $request)
    {
        try {
            $zip = new ZipArchive();
            $status = $zip->open($request->file("theme")->getRealPath());
            if ($status !== true) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('themes.theme_upload_error'),
                    'error' => $status
                ]);
            }
            else{
                $zip->extractTo(base_path());
                $zip->close();
            }
            $json = file_get_contents(base_path('theme.json'));
            $theme = new Themes();
            $theme->name = json_decode($json)->name;
            $theme->is_default = false;
            $theme->save();
            unlink(base_path('theme.json'));
            return response()->json([
                'status' => 'success',
                'message' => __('themes.theme_save_success')
            ]);
        }
        catch (Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => __('themes.theme_save_error'),
                'error' => $e->getMessage()
            ]);
        }
    }
    public function delete(Request $request, Themes $themes, File $file)
    {
        try {
            $theme = $themes::where('_id', $request->post('id'))->first();
            if($theme->is_default){
                return response()->json([
                    'status' => 'error',
                    'message' => __('themes.theme_has_default')
                ]);
            }
            $theme_public_path = public_path('themes/' . $theme->name);
            $theme_resource_path = resource_path('views/' . $theme->name);
            if ($file->exists($theme_public_path)) {
                $file->deleteDirectory($theme_public_path);
            }
            if ($file->exists($theme_resource_path)) {
                $file->deleteDirectory($theme_resource_path);
            }
            $theme->delete();
            return response()->json([
                'status' => 'success',
                'message' => __('themes.delete_success')
            ]);
        }
        catch (Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => __('themes.delete_error'),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function makeDefault(Themes $theme)
    {
        try {
            Themes::where('is_default', true)->update(['is_default' => false]);
            $theme->is_default = true;
            $theme->save();
            return back()->with('success', __('themes.theme_default_success'));
        }
        catch (Exception $e){
            return back()->with('error', __('themes.theme_save_error'). ' ' . $e->getMessage());
        }
    }
}