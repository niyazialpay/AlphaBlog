<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HomePosts extends Component
{
    public mixed $paginate;

    /**
     * Create a new component instance.
     */
    public function __construct($paginate = 10)
    {
        $this->paginate = $paginate;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('themes.'.app('theme')->name.'.components.posts.home-posts', [
            'posts' => Posts::with(['categories', 'user'])
                ->where('post_type', 'post')
                ->where('language', session('language'))
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->paginate($this->paginate)->withQueryString(),
        ]);
    }
}
