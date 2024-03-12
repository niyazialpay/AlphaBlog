<?php

namespace App\Http\Controllers\SiteMap;

use App\Http\Controllers\Controller;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use App\Models\User;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    protected string $contentType = 'text/xml';
    public function index()
    {
        return response()->view('sitemap.index')
            ->header('content-type', $this->contentType);
    }

    public function categories($language){
        return response()
            ->view('sitemap.categories', [
                'categories' => Categories::where('language', $language)->get(),
            ])
            ->header('content-type', $this->contentType);
    }

    public function posts($language){
        return response()
            ->view('sitemap.posts', [
                'posts' => Posts::where('language', $language)->where('is_published', true)->get(),
            ])
            ->header('content-type', $this->contentType);
    }

    public function users($language){
        return response()
            ->view('sitemap.users', [
                'users' => User::all(),
                'language' => $language,
            ])
            ->header('content-type', $this->contentType);
    }
}
