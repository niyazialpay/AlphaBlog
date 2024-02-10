<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SocialMenu extends Component
{
    protected array $show_header = [];
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->show_header = [
            'linkedin',
            'github',
            'instagram',
            'x',
            'facebook',
            'devto',
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view(app('theme')->name.'.components.social-menu', [
            'show_header' => $this->show_header
        ]);
    }
}
