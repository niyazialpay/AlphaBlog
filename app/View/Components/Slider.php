<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Slider extends Component
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
        try{
            return view('themes.'.app('theme')->name.'.components.slider', [
                'slider' => Posts::with('media', 'media.model', 'user')
                    ->where('is_published', true)
                    ->where('language', session('language'))
                    ->where('post_type', 'post')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
            ]);
        }
        catch (\Exception $exception){
            return view('Default.components.slider', [
                'slider' => Posts::with('media', 'media.model', 'user')
                    ->where('is_published', true)
                    ->where('language', session('language'))
                    ->where('post_type', 'post')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
            ]);
        }
    }
}
