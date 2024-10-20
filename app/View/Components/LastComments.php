<?php

namespace App\View\Components;

use App\Models\Post\Comments;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class LastComments extends Component
{
    public $lastComments;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        if (Cache::has(config('cache.prefix').'last_comments_'.session('language'))) {
            $lastComments = Cache::get(config('cache.prefix').'last_comments_'.session('language'));
        } else {
            $lastComments = Comments::with([
                'post.categories',
            ])->join('posts', 'comments.post_id', '=', 'posts.id')
                ->join('media', 'posts.id', '=', 'media.model_id')
                ->select(['comments.*', 'posts.title', 'posts.slug', 'posts.language', 'media.file_name', 'media.id as media_id', 'users.nickname as user_nickname', 'users.email as user_email'])
                ->leftJoin('users', 'comments.user_id', '=', 'users.id')
                ->where('media.model_type', 'App\Models\Post\Posts')
                ->where('media.collection_name', 'posts')
                ->where('comments.is_approved', 1)
                ->where('posts.is_published', 1)
                ->where('posts.language', session('language'))
                ->orderBy('comments.created_at', 'desc')
                ->limit(10)
                ->get();

            Cache::put(config('cache.prefix').'last_comments_'.session('language'), $lastComments, now()->addDay());
        }
        $this->lastComments = $lastComments;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        try {
            return view('themes.'.app('theme')->name.'.components.posts.last-comments');
        } catch (Exception $exception) {
            return view('Default.components.posts.last-comments');
        }
    }
}
