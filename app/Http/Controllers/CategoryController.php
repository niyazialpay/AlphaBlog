<?php

namespace App\Http\Controllers;

use App\Support\ThemeData;
use App\Support\ThemeManager;

class CategoryController extends Controller
{
    public function show($language, $categories, $showCategory)
    {
        if (ThemeManager::usingVue()) {
            return ThemeManager::render('categories', array_merge(
                ThemeData::categoryDetail($showCategory),
                [
                    'pageMeta' => ThemeData::metaForCategory($showCategory),
                ]
            ));
        }

        return ThemeManager::render('categories', [
            'category' => $showCategory,
        ]);
    }
}
