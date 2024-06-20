<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class MostReadPosts extends Component
{
    public mixed $posts;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        if (Cache::has(config('cache.prefix').'most_read_posts_'.session('language'))) {
            $posts = Cache::get(config('cache.prefix').'most_read_posts_'.session('language'));
        } else {
            $posts = Posts::with('categories')
                ->join('media', 'posts.id', '=', 'media.model_id')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select(['posts.*', 'media.file_name', 'media.id as media_id', 'users.nickname', 'users.email'])
                ->where('media.model_type', 'App\Models\Post\Posts')
                ->where('posts.is_published', 1)
                ->where('posts.post_type', 'post')
                ->where('posts.language', session('language'))
                ->where('posts.created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->orderBy('posts.views', 'desc')
                ->limit(10)
                ->get();

            Cache::put(config('cache.prefix').'most_read_posts_'.session('language'), $posts, now()->addDay());
        }
        $this->posts = $posts;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        try {
            return view('themes.'.app('theme')->name.'.components.posts.most-read-posts');
        } catch (\Exception $exception) {
            return view('Default.components.posts.most-read-posts');
        }
    }
}
