<?php

namespace App\Http\Controllers;

use App\Support\ThemeData;
use App\Support\ThemeManager;
use Illuminate\Support\Facades\Cookie;

class PostController extends Controller
{
    public function show($language, $showPost)
    {
        if (! Cookie::has($showPost->slug)) {
            $showPost->increment('views');
            $showPost->save();
            Cookie::queue(Cookie::make($showPost->slug, true, 7200, null, null, true, true));
        }

        if (ThemeManager::usingVue()) {
            return ThemeManager::render('post', [
                'post' => ThemeData::postDetail($showPost),
                'relatedPosts' => ThemeData::relatedPosts($showPost),
            ]);
        }

        return ThemeManager::render('post', [
            'post' => $showPost,
            'ignore_minify' => true,
        ]);
    }
}
