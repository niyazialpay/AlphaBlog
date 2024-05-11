<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Exception;
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
        $post = Posts::with('categories')->join('users', 'posts.user_id', '=', 'users.id')
            ->join('media', 'posts.id', '=', 'media.model_id')
            ->select([
                'posts.*',
                'users.nickname',
                'users.email',
                'media.file_name',
                'media.id as media_id'
            ])
            ->where('posts.post_type', 'post')
            ->where('posts.language', session('language'))
            ->where('posts.is_published', true)
            ->where('media.collection_name', 'posts')
            ->orderBy('posts.created_at', 'desc')
            ->paginate($this->paginate)->withQueryString();
        try {
            return view('themes.'.app('theme')->name.'.components.posts.home-posts', [
                'posts' => $post,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.posts.home-posts', [
                'posts' => $post,
            ]);
        }
    }
}
