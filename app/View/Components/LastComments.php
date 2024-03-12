<?php

namespace App\View\Components;

use App\Models\Post\Comments;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LastComments extends Component
{
    public $lastComments;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->lastComments = Comments::with([
            'post' => function ($query) {
                $query->select('title', 'slug', 'language');
            },
            'user' => function ($query) {
                $query->select('nickname', 'email');
            },
            'post.media',
            'post.media.model'
        ])->where('is_approved', true)
        ->whereHas('post', function ($query) {
            $query->where('is_published', true)->where('language', session('language'));
        })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view(app('theme')->name.'.components.posts.last-comments');
    }
}
