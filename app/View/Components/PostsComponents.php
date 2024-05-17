<?php

namespace App\View\Components;

use App\Models\OneSignal;
use App\Models\Post\Posts;
use App\Models\Search;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PostsComponents extends Component
{
    public mixed $category;

    public int $paginate;

    public mixed $user;

    public ?string $search;

    /**
     * Create a new component instance.
     */
    public function __construct($category = null, $paginate = 10, $user = null, $search = null)
    {
        $this->category = $category;
        $this->paginate = $paginate;
        $this->user = $user;
        $this->search = $search;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $search = replace_characters(GetPost($this->search));
        try {
            $page = request()->get('page') ?? 1;
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $page = 1;
        }
        if ($search) {
            $str = new Str();
            if(Cache::has(config('cache.prefix').'search_'.$str::slug($search).session('language').'_page_'.$page.$this->paginate)){
                $posts = Cache::get(config('cache.prefix').'search_'.$str::slug($search).session('language').'_page_'.$page.$this->paginate);
            }
            else{
                $posts = Posts::search($search)
                    ->query(function ($query) {
                        $query->with(['user', 'categories']);
                    })
                    ->where('post_type', 'post')
                    ->where('language', session('language'))
                    ->where('is_published', 1)
                    ->orderBy('created_at', 'desc')
                    ->paginate($this->paginate)->withQueryString();
                Cache::put(config('cache.prefix').'search_'.$str::slug($search).session('language').'_page_'.$page.$this->paginate, $posts, now()->addDay());
            }
            if ($posts->count() == 0) {
                $search_model = new Search();
                $searched_word = $search_model::where('search', $search)->count();
                if ($searched_word == 0) {
                    $search_model::create([
                        'search' => $search,
                        'language' => session('language'),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    OneSignal::sendPush([
                        'en' => $search,
                    ], [
                        'en' => __('search.notification', ['search' => $search]),
                    ]);
                }
            }
        } else {
            if(Cache::has(config('cache.prefix').'posts_'.session('language').'_page_'.$page.$this->category.$this->user.$this->paginate)){
                $posts = Cache::get(config('cache.prefix').'posts_'.session('language').'_page_'.$page.$this->category.$this->user.$this->paginate);
            }
            else{
                $posts = Posts::with(['user', 'categories'])
                    ->where('post_type', 'post')
                    ->when($this->category,
                        function ($query, $category) {
                            return $query->whereHas('categories', function ($query) use ($category) {
                                $query->where('category_id', $category->id);
                            });
                        })
                    ->when($this->user,
                        function ($query, $user) {
                            return $query->where('user_id', $user->id);
                        })
                    ->where('language', session('language'))
                    ->where('is_published', 1)
                    ->orderBy('created_at', 'desc')
                    ->paginate($this->paginate)->withQueryString();
                Cache::put(config('cache.prefix').'posts_'.session('language').'_page_'.$page.$this->category.$this->user.$this->paginate, $posts, now()->addDay());
            }
        }

        try {
            return view('themes.'.app('theme')->name.'.components.posts.posts', [
                'posts' => $posts,
                'search' => $search,
                'category' => $this->category,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.posts.posts', [
                'posts' => $posts,
                'search' => $search,
                'category' => $this->category,
            ]);
        }
    }
}
