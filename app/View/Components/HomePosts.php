<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
        try {
            $page = request()->get('page') ?? 1;
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $page = 1;
        }
        if (Cache::has(config('cache.prefix').'home_posts_'.session('language').'_page_'.$page.$this->paginate)) {
            $post = Cache::get(config('cache.prefix').'home_posts_'.session('language').'_page_'.$page.$this->paginate);
        } else {
            $post = Posts::with('categories')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->join('media', 'posts.id', '=', 'media.model_id')
                ->select([
                    'posts.*',
                    'users.nickname',
                    'users.email',
                    'media.file_name',
                    'media.id as media_id',
                ])
                ->where('posts.post_type', 'post')
                ->where('posts.language', session('language'))
                ->where('posts.is_published', true)
                ->where('media.collection_name', 'posts')
                ->where('posts.created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->orderBy('posts.created_at', 'desc')
                ->paginate($this->paginate)->withQueryString();

            Cache::put(config('cache.prefix').'home_posts_'.session('language').'_page_'.$page.$this->paginate, $post, now()->addDay());
        }
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
