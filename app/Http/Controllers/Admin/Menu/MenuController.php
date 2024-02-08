<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\MenuRequest;
use App\Models\Menu\Menu;
use Illuminate\Http\Request;

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
        return response()->json([
            'message' => __('menu.menu_saved'),
            'status' => 'success',
        ]);
    }

    public function delete(Request $request)
    {
        $menu = Menu::find($request->post('menu_id'));
        $menu->delete();
        return response()->json([
            'message' => __('menu.menu_deleted'),
            'status' => 'success',
        ]);
    }
}
