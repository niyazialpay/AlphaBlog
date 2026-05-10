<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ThemeData;
use App\Support\ThemeManager;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function __construct() {}

    public function index(): Response
    {
        if (ThemeManager::usingVue()) {
            $generalSettings = app('general_settings');
            $totalArticles = max(1, (int) ($generalSettings->homepage_featured_count ?? 20));
            $articles = ThemeData::recentPosts($totalArticles, 0);

            $authors = User::withCount('posts')
                ->with('social')
                ->whereNot('role', 'user')
                ->orderByDesc('posts_count')
                ->limit(8)
                ->get()
                ->map(fn (User $u) => ThemeData::authorSummary($u))
                ->values()
                ->toArray();

            return ThemeManager::render('home', [
                'articles' => $articles,
                'authors' => $authors,
                'categories' => ThemeData::topCategories(8),
                'pageMeta' => ThemeData::metaForHome(),
            ]);
        }

        return ThemeManager::render('home', [
            'category' => null,
        ]);
    }
}
