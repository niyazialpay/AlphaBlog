<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;
use Meilisearch\Client;
use Meilisearch\Meilisearch;

class SimilarPosts extends Component
{
    public $post;
    /**
     * Create a new component instance.
     */
    public function __construct($post, $limit)
    {
        if(Cache::has(config('cache.prefix').'similar_posts_'.$post->_id.'_'.$limit)){
            $this->post = Cache::get(config('cache.prefix').'similar_posts_'.$post->_id.'_'.$limit);
        }
        else {
            $this->post = Cache::rememberForever(config('cache.prefix').'similar_posts_'.$post->id.'_'.$limit,
                function() use($post, $limit){
                $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
                $index = $client->index(config('scout.prefix').'posts');

                $post_ids = [];
                foreach ($post->meta_keywords as $keyword) {
                    $search = $index->search($keyword)->getHits();
                    foreach ($search as $result) {
                        $post_ids[] = $result['_id'];
                    }
                }

                $title_parse = explode(' ', $post->title);
                foreach ($title_parse as $keyword) {
                    $search = $index->search($keyword)->getHits();
                    foreach ($search as $result) {
                        $post_ids[] = $result['_id'];
                    }
                }

                $post_ids = array_unique($post_ids);

                return Posts::where('post_type', 'post')
                    ->where('language', session('language'))
                    ->where('is_published', true)
                    ->where('slug', '!=', $post->slug)
                    ->whereIn('_id', $post_ids)
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
        return view(app('theme')->name . '.components.posts.similar-posts', [
            'posts' => $this->post,
        ]);
    }
}