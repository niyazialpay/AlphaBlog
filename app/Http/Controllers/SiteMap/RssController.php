<?php

namespace App\Http\Controllers\SiteMap;

use App\Http\Controllers\Controller;
use App\Models\Post\Posts;

class RssController extends Controller
{
    public function show($language)
    {
        return response()->view('sitemap.rss', [
            'posts' => Posts::with(['user', 'categories'])
                ->where('is_published', true)
                ->where('language', $language)
                ->where('post_type', 'post')
                ->orderBy('created_at', 'desc')
                ->get(),
        ])->header('Content-Type', 'text/xml');
    }
}
