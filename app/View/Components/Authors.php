<?php

namespace App\View\Components;

use App\Models\User;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class Authors extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        if(Cache::has('authors_'.session('language'))) {
            $authors = Cache::get('authors');
        } else {
            $authors = User::withCount(['posts' => function($q){
                $q->where('is_published', true);
                $q->where('type', 'post');
                $q->where('language', session('language'));
            }])->orderBy('posts_count', 'desc')->limit(5)->get();
            Cache::put('authors_'.session('language'), $authors, 60);
        }
        try {
            return view('themes.'.app('theme')->name.'.components.authors', [
                'authors' => $authors,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.authors', [
                'authors' => $authors,
            ]);
        }
    }
}
