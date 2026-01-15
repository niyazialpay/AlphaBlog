<?php

namespace App\Http\Controllers;

use App\Support\ThemeData;
use App\Support\ThemeManager;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Show the application dashboard.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        if (ThemeManager::usingVue()) {
            return ThemeManager::render('home', [
                'featuredPosts' => ThemeData::featuredPosts(6),
                'recentPosts' => ThemeData::recentPosts(9, 5),
                'categories' => ThemeData::topCategories(8),
                'pageMeta' => ThemeData::metaForHome(),
            ]);
        }

        return ThemeManager::render('home', [
            'category' => null,
        ]);
    }
}
