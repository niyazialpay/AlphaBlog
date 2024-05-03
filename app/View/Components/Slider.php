<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Exception;
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
        $post = Posts::select([
            'posts.*',
            'media.file_name',
            'media.id as media_id',
            'users.nickname',
            'users.email'
        ])
            ->join('media', 'media.model_id', '=', 'posts.id')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->where('media.model_type', 'App\Models\Post\Posts')
            ->where('is_published', true)
            ->where('language', session('language'))
            ->where('post_type', 'post')
            ->where('media.collection_name', 'posts')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        /*$post = Posts::where('is_published', true)
            ->where('language', session('language'))
            ->where('post_type', 'post')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();*/
        try {
            return view('themes.'.app('theme')->name.'.components.slider', [
                'slider' => $post,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.slider', [
                'slider' => $post,
            ]);
        }
    }
}
