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
            $postDetail = ThemeData::postDetail($showPost);
            $structuredData = $postDetail['structuredData'] ?? null;
            unset($postDetail['structuredData']);

            return ThemeManager::render('post', [
                'post' => $postDetail,
                'relatedPosts' => ThemeData::relatedPosts($showPost),
                'pageMeta' => ThemeData::metaForPost($showPost),
                'structuredData' => $structuredData,
            ]);
        }

        return ThemeManager::render('post', [
            'post' => $showPost,
            'ignore_minify' => true,
        ]);
    }
}
