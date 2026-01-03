<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuRequest;
use App\Models\Menu\Menu;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index(Menu $menu)
    {
        return view('panel.menu.index', [
            'all_menus' => new Menu,
            'menu' => $menu,
        ]);
    }

    public function save(Menu $menu, MenuRequest $request)
    {
        try {
            DB::beginTransaction();
            $menu->fill($request->except('_token'));
            $menu->save();
            Cache::forget(config('cache.prefix').'header_menu_'.$menu->language);
            Cache::forget(config('cache.prefix').'footer_menu_'.$menu->language);
            DB::commit();

            return response()->json([
                'message' => __('menu.menu_saved'),
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('menu.menu_save_error'),
                'status' => 'error',
            ]);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $menu = Menu::find($request->post('menu_id'));
            Cache::forget(config('cache.prefix').'header_menu_'.$menu->language);
            Cache::forget(config('cache.prefix').'footer_menu_'.$menu->language);
            $menu->menuItems()->delete();
            $menu->delete();
            DB::commit();

            return response()->json([
                'message' => __('menu.menu_deleted'),
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('menu.menu_delete_error'),
                'status' => 'error',
            ]);
        }
    }
}
