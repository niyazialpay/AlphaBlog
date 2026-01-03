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
    public int $paginate;
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
            if(is_numeric(request()->get('page'))) {
                $page = request()->get('page') ?? 1;
            }
            else{
                $page = 1;
            }
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $page = 1;
        }

        $cacheKey = config('cache.prefix').'home_posts_'.session('language').'_page_'.$page.'_'.$this->paginate.'_skip_'.$this->skip;

        if (Cache::has($cacheKey)) {
            $posts = Cache::get($cacheKey);
        } else {
            $perPage = $this->paginate;
            $skip = ($page - 1) * $perPage + $this->skip;

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
                ->orderBy('posts.created_at', 'desc')
                ->skip($skip)
                ->take($perPage)
                ->get();

            // Pagination manually handled
            $total = Posts::where('post_type', 'post')
                ->where('language', session('language'))
                ->where('is_published', true)
                ->where('created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->count();

            $posts = new LengthAwarePaginator(
                $postsQuery,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

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
