<?php

namespace App\Http\Controllers;

use Exception;

class CategoryController extends Controller
{
    public function show($language, $categories, $showCategory)
    {
        try {
            return response()->view('themes.'.app('theme')->name.'.categories', [
                'category' => $showCategory,
            ]);
        } catch (Exception $e) {
            return response()->view('Default.categories', [
                'category' => $showCategory,
            ]);
        }
    }
}
