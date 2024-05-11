<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Meilisearch\Client;

class SimilarPosts extends Component
{
    public $post;

    /**
     * Create a new component instance.
     */
    public function __construct($post, $limit)
    {
        if (Cache::has(config('cache.prefix').'similar_posts_'.$post->id.'_'.$limit)) {
            $this->post = Cache::get(config('cache.prefix').'similar_posts_'.$post->id.'_'.$limit);
        } else {
            $this->post = Cache::rememberForever(config('cache.prefix').'similar_posts_'.$post->id.'_'.$limit,
                function () use ($post, $limit) {
                    $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
                    $index = $client->index(config('scout.prefix').'posts');

                    $post_ids = [];
                    foreach (explode(',', $post->meta_keywords) as $keyword) {
                        $search = $index->search($keyword)->getHits();
                        foreach ($search as $result) {
                            $post_ids[] = $result['id'];
                        }
                    }

                    $title_parse = explode(' ', $post->title);
                    foreach ($title_parse as $keyword) {
                        $search = $index->search($keyword)->getHits();
                        foreach ($search as $result) {
                            $post_ids[] = $result['id'];
                        }
                    }

                    $post_ids = array_unique($post_ids);

                    return Posts::where('post_type', 'post')
                        ->where('language', session('language'))
                        ->where('is_published', 1)
                        ->where('slug', '!=', $post->slug)
                        ->whereIn('id', $post_ids)
                        ->orderBy('created_at', 'desc')
                        ->take($limit)
                        ->get();
                });
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        try {
            return view('themes.'.app('theme')->name.'.components.posts.similar-posts', [
                'posts' => $this->post,
            ]);
        } catch (Exception $e) {
            return view('Default.components.posts.similar-posts', [
                'posts' => $this->post,
            ]);
        }
    }
}
