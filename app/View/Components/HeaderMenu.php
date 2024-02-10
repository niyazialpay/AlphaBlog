<?php

namespace App\View\Components;

use App\Models\Menu\Menu;
use App\Models\Themes;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeaderMenu extends Component
{
    protected Closure $menuItems;
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
        $items = Menu::with(['menuItems' => function($query){
            $query->where('parent_id', null);
        }])->where('language', session()->get('language'))
            ->where('menu_position', 'header')
            ->first();
        $menu_items = $items->menuItems;

        return view(app('theme')->name.'.components.menu.header_menu', [
            'menu_items' => $menu_items
        ]);
    }
}
