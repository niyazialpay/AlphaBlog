<?php

namespace App\View\Components;

use App\Models\Comments;
use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class NewComments extends Component
{
    public array|object|null $newComments;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->newComments = Comments::with([
            'post' => function ($query) {
                $query->select('id', 'title');
            },
            'user' => function ($query) {
                $query->select('id', 'nickname', 'email');
            }
        ])->where('is_approved', false)->orderBy('created_at', 'desc')->limit(5)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.new-comments', [
            'new_comments' => $this->newComments
        ]);
    }
}
