<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ThemeData;
use App\Support\ThemeManager;

class UserController extends Controller
{
    public function posts($language, $user, User $users)
    {
        if (ThemeManager::usingVue()) {
            $users->loadMissing('social');

            return ThemeManager::render('user-posts', [
                'author' => ThemeData::authorSummary($users),
                'posts' => ThemeData::postsForUser($users),
                'pageMeta' => ThemeData::metaForUser($users),
            ]);
        }

        return ThemeManager::render('user-posts', [
            'user' => $users,
        ]);
    }
}
