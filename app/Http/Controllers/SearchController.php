<?php

namespace App\Http\Controllers;

class SearchController extends Controller
{
    public function index($language, $search_result, $search_term = null)
    {

        return view('themes.'.app('theme')->name.'.search', [
            'search' => $search_term,
        ]);
    }
}
