<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Menu\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function menu($language){
        $menu = Menu::with(['menuItems.children', 'menuItems' => function ($query) {
            $query->where('parent_id', null);
        }])->where('language', session('language'))
            ->where('menu_position', 'header')
            ->first();

        if ($menu) {
            $menu_items = $menu->menuItems;
        } else {
            $menu_items = [];

        }

        return response()->json($menu_items, 200, [], JSON_PRETTY_PRINT);
    }
}
