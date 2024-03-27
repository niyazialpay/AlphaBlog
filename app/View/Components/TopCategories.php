<?php

namespace App\View\Components;

use App\Models\Post\Categories;
use Closure;
use Illuminate\Contracts\View\View;
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
        $topCategories = Categories::raw(function ($collection) use ($language) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'language' => $language,
                    ],
                ],
                [
                    '$unwind' => [
                        'path' => '$post_id',
                    ],
                ],
                [
                    '$group' => [
                        '_id' => '$_id',
                        'name' => ['$first' => '$name'],
                        'slug' => ['$first' => '$slug'],
                        'post_id_count' => ['$sum' => 1],
                    ],
                ],
                [
                    '$sort' => [
                        'post_id_count' => -1,
                    ],
                ],
                [
                    '$limit' => 6,
                ],
            ]);
        });
        return view('themes.'.app('theme')->name.'.components.top-categories', [
            'categories' =>  $topCategories
        ]);
    }
}
