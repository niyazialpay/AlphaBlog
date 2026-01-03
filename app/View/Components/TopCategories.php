<?php

namespace App\View\Components;

use App\Models\Post\Categories;
use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class TopCategories extends Component
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
        $language = session('language');

        if (Cache::has(config('cache.prefix').'top_categories_'.$language)) {
            $topCategories = Cache::get(config('cache.prefix').'top_categories_'.$language);
        } else {
            $topCategories = Categories::withCount('posts')
                ->where('language', $language)
                ->orderBy('posts_count', 'desc')
                ->limit(6)
                ->get();

            Cache::put(config('cache.prefix').'top_categories_'.$language, $topCategories, now()->addDay());
        }

        try {
            return view('themes.'.app('theme')->name.'.components.top-categories', [
                'categories' => $topCategories,
            ]);
        } catch (Exception $exception) {
            return view('Default.components.top-categories', [
                'categories' => $topCategories,
            ]);
        }
    }
}
