<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use App\Models\Search;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

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
        $search = $this->search;
        if ($search) {
            $posts = Posts::search(replace_characters(GetPost($search)))
                ->query(function ($query) {
                    $query->with(['user', 'categories']);
                })
                ->where('post_type', 'post')
                ->where('language', session('language'))
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->paginate($this->paginate)->withQueryString();
            if($posts->count()==0){
                $search_model = new Search();
                $searched_word = $search_model::where('search', $search)->count();
                if($searched_word==0){
                    $search_model::create([
                        'search' => $search,
                        'language' => session('language'),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                }
            }
        } else {
            $posts = Posts::with(['user', 'categories'])
                ->where('post_type', 'post')
                ->when($this->category,
                    function ($query, $category) {
                        return $query->whereIn('category_id', [$category->_id]);
                    })
                ->when($this->user,
                    function ($query, $user) {
                        return $query->where('user_id', $user->_id);
                    })
                ->where('language', session('language'))
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->paginate($this->paginate)->withQueryString();
        }

        return view('themes.'.app('theme')->name.'.components.posts.posts', [
            'posts' => $posts,
            'search' => $search,
            'category' => $this->category,
        ]);
    }
}
