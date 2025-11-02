<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ThemeData;
use App\Support\ThemeManager;

class AuthorsController extends Controller
{
    public function index()
    {
        $authors = User::withCount('posts')
            ->with('social')
            ->whereNot('role', 'user')
            ->paginate();

        if (ThemeManager::usingVue()) {
            return ThemeManager::render('authors', [
                'authors' => ThemeData::authorsPaginatorToArray($authors),
                'pageMeta' => ThemeData::metaForAuthors(),
            ]);
        }

        return ThemeManager::render('authors', [
            'authors' => $authors,
        ]);
    }
}
