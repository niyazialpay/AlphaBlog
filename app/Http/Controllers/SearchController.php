<?php

namespace App\Http\Controllers;

use Exception;

class SearchController extends Controller
{
    public function index($language, $search_result, $search_term = null)
    {
        try {
            return response()->view('themes.'.app('theme')->name.'.search', [
                'search' => $search_term,
            ]);
        } catch (Exception $e) {
            return response()->view('Default.search', [
                'search' => $search_term,
            ]);
        }
    }
}
