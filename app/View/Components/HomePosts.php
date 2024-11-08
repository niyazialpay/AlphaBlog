<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HomePosts extends Component
{
    public mixed $paginate;
    public int  $skip = 0;

    /**
     * Create a new component instance.
     */
    public function __construct($paginate = 10, $skip = 0)
    {
        $this->paginate = $paginate;
        $this->skip = $skip;
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

        $cacheKey = config('cache.prefix').'home_posts_'.session('language').'_page_'.$page.'_'.$this->paginate;

        if (Cache::has($cacheKey)) {
            $posts = Cache::get($cacheKey);
        } else {
            $perPage = $this->paginate;

            $postsQuery = Posts::with('categories')
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
                ->orderBy('posts.created_at', 'desc');

            // Burada paginate() metodu kullanılıyor
            $posts = $postsQuery->paginate($perPage);

            Cache::put($cacheKey, $posts, now()->addHours(12));
        }

        try {
            return view('themes.'.app('theme')->name.'.components.posts.home-posts', [
                'posts' => $posts,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.posts.home-posts', [
                'posts' => $posts,
            ]);
        }

    }
}
