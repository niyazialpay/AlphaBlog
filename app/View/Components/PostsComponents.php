<?php

namespace App\View\Components;

use App\Models\Post\Posts;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PostsComponents extends Component
{

    public mixed $category;
    public int $paginate;
    public mixed $user;
    public string|null $search;
    /**
     * Create a new component instance.
     */
    public function __construct($category=null, $paginate=10, $user=null, $search=null)
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
        if($search){
            $posts = Posts::search(GetPost($search))
                ->query(function ($query) {
                    $query->with(['user', 'categories']);
                })
                ->where('post_type', 'post')
                ->where('language', session('language'))
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->paginate($this->paginate)->withQueryString();
        }
        else{
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
        return view(app('theme')->name . '.components.posts.posts', [
            'posts' => $posts,
            'search' => $search,
            'category' => $this->category
        ]);
    }
}