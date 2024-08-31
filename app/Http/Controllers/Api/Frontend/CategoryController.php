<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post\Categories;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index($language)
    {
        $categories = Categories::with(['categoryMedia' => function ($query) {
            //$query->select('original_url');
        }])->where('language', $language)->get();

        return response()->json($categories);
    }

    public function topCategories($language)
    {
        return response()->json(Categories::with('categoryMedia')->withCount('posts')
            ->where('language', $language)
            ->orderBy('posts_count', 'desc')
            ->limit(6)
            ->get());
    }

    public function show(Request $request, $slug)
    {
        /*$categories = Categories::with(['posts' => function($query){
            $query->where('is_published', true)->where('language', 'tr')->paginate(30);
        }])->where('slug', $slug)->get();*/
        $category = Categories::where('slug', $slug)->where('language', 'tr')->first();
        $posts = $category->posts()->with('user')->where('is_published', true)->where('language', 'tr')->paginate(30);
        $posts->getCollection()->transform(function ($post) {
            $post->title = stripslashes($post->title);
            $post->content = stripslashes($post->content);

            return $post;
        });

        return response()->json([
            'category' => $category,
            'posts' => $posts,
        ]);
    }
}
