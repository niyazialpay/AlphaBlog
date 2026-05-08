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
            $featured = ThemeData::featuredPosts(5);
            $recent = ThemeData::recentPosts(15, 5);

            $featuredIds = array_column($featured, 'id');
            $rest = array_values(array_filter($recent, fn ($p) => ! in_array($p['id'], $featuredIds)));
            $articles = array_values(array_merge($featured, $rest));

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
