<?php

namespace App\View\Components\Menu;

use App\Models\Menu\Menu;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class FooterMenu extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        /*if(Cache::has(config('cache.prefix').'footer_menu_'.session('language'))){
            $menu = Cache::get(config('cache.prefix').'footer_menu_'.session('language'));
        }
        else{
            $menu = Cache::rememberForever(config('cache.prefix').'footer_menu_'.session('language'), function(){
                return Menu::with(['menuItems' => function($query){
                    $query->where('parent_id', null);
                }])->where('language', session('language'))
                    ->where('menu_position', 'footer')
                    ->get();
            });
        }*/

        return view(app('theme')->name.'.components.menu.footer-menu',  [
            'menu' => Menu::with(['menuItems' => function($query){
                $query->where('parent_id', null);
            }])->where('language', session('language'))
                ->where('menu_position', 'footer')
                ->get(),
        ]);
    }
}
