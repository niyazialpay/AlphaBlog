<?php

namespace App\View\Components\Menu;

use App\Models\Menu\Menu;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class HeaderMenu extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        /*if(Cache::has(config('cache.prefix').'header_menu_'.session('language'))){
            $menu = Cache::get(config('cache.prefix').'header_menu_'.session('language'));
        }
        else{
            $menu = Cache::rememberForever(config('cache.prefix').'header_menu_'.session('language'), function(){
                return Menu::with(['menuItems.children', 'menuItems' => function($query){
                    $query->where('parent_id', null);
                }])->where('language', session('language'))
                    ->where('menu_position', 'header')
                    ->first();
            });
        }*/

        $menu = Menu::with(['menuItems.children', 'menuItems' => function($query){
            $query->where('parent_id', null);
        }])->where('language', session('language'))
            ->where('menu_position', 'header')
            ->first();

        if($menu){
            $menu_items = $menu->menuItems;
        }
        else{
            $menu_items = [];

        }

        return view(app('theme')->name.'.components.menu.header_menu', [
            'menu' => $menu,
            'menu_items' => $menu_items
        ]);
    }
}
