<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MostReadPosts extends Component
{
    public mixed $posts;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->posts = Posts::with(['user', 'categories', 'media', 'media.model'])
            ->where('is_published', true)
            ->where('post_type', 'post')
            ->where('language', session('language'))
            ->orderBy('views', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        try{
            return view('themes.'.app('theme')->name.'.components.posts.most-read-posts');
        }
        catch (\Exception $exception){
            return view('Default.components.posts.most-read-posts');
        }
    }
}
