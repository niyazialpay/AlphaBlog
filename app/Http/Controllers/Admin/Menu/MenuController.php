<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuRequest;
use App\Models\Menu\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function index(Menu $menu)
    {
        return view('panel.menu.index', [
            'all_menus' => new Menu(),
            'menu' => $menu,
        ]);
    }

    public function save(Menu $menu, MenuRequest $request)
    {
        $menu->fill($request->except('_token'));
        $menu->save();
        Cache::forget(config('cache.prefix').'header_menu_'.$menu->language);
        Cache::forget(config('cache.prefix').'footer_menu_'.$menu->language);
        return response()->json([
            'message' => __('menu.menu_saved'),
            'status' => 'success',
        ]);
    }

    public function delete(Request $request)
    {
        $menu = Menu::find($request->post('menu_id'));
        Cache::forget(config('cache.prefix').'header_menu_'.$menu->language);
        Cache::forget(config('cache.prefix').'footer_menu_'.$menu->language);
        $menu->menuItems()->delete();
        $menu->delete();
        return response()->json([
            'message' => __('menu.menu_deleted'),
            'status' => 'success',
        ]);
    }
}
