<?php

namespace App\View\Components;

use App\Models\User;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Authors extends Component
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
        $authors = User::withCount('posts')->orderBy('posts_count', 'desc')->limit(10)->get();
        try {
            return view('themes.'.app('theme')->name.'.components.authors', [
                'authors' => $authors,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.authors', [
                'authors' => $authors,
            ]);
        }
    }
}
