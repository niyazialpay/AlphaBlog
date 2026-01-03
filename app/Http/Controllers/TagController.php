<?php

namespace App\Http\Controllers;

use App\Support\ThemeData;
use App\Support\ThemeManager;

class TagController extends Controller
{
    public function show($language, $tags, $showTag)
    {
        if (ThemeManager::usingVue()) {
            return ThemeManager::render('tags', [
                'posts' => ThemeData::postsFromPaginator($showTag),
                'tag' => request()->segment(3),
                'pageMeta' => ThemeData::metaForTag(urldecode((string) request()->segment(3))),
            ]);
        }

        return ThemeManager::render('tags', [
            'posts' => $showTag,
            'tag' => request()->segment(3),
        ]);
    }
}
