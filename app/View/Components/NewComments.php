<?php

namespace App\View\Components;

use App\Models\Post\Comments;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NewComments extends Component
{
    public array|object|null $newComments;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $comments_class = Comments::class;
        $comments = $comments_class::with([
            'post' => function ($query) {
                $query->select('id', 'title');
            },
            'user' => function ($query) {
                $query->select('id', 'nickname', 'email');
            }
        ])->where('is_approved', false);
        if(auth()->user()->can('view', $comments_class)){
            $this->newComments = $comments->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        else{
            $this->newComments = $comments->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('panel.components.new-comments', [
            'new_comments' => $this->newComments
        ]);
    }
}
