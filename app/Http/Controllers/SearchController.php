<?php

namespace App\Http\Controllers;

use App\Support\ThemeData;
use App\Support\ThemeManager;

class SearchController extends Controller
{
    public function index($language, $search_result, $search_term = null)
    {
        if (ThemeManager::usingVue()) {
            return ThemeManager::render('search', [
                'search' => $search_term,
                'results' => ThemeData::searchPosts($search_term),
            ]);
        }

        return ThemeManager::render('search', [
            'search' => $search_term,
        ]);
    }
}

