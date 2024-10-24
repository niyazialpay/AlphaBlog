<?php

namespace App\View\Components\Menu;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SocialMenu extends Component
{
    public $show;

    /**
     * Create a new component instance.
     */
    public function __construct($show)
    {
        $this->show = $show;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        try {
            return view('themes.'.app('theme')->name.'.components.menu.social-menu');
        } catch (\Exception $exception) {
            return view('Default.components.menu.social-menu');
        }
    }
}
