<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post\Posts;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Posts::with([
            'categories' => function($query){
                $query->select(['name', 'slug']);
            },
            'user' => function($query){
                $query->select(['nickname', 'id']);
            }
        ])->select(['title', 'slug', 'id', 'user_id'])->where('language', 'tr')->where('is_published', true)->where('post_type', 'post')->orderBy('created_at', 'desc')->paginate(30);

        $posts->getCollection()->transform(function ($post) {
            $post->title = stripslashes($post->title);
            $post->content = stripslashes($post->content);
            return $post;
        });

        return response()->json($posts);
    }

    public function show(Request $request, $slug)
    {
        $post = Posts::with([
            'categories' => function($query){
                $query->select(['name', 'slug']);
            },
            'user' => function($query){
                //$query->select(['nickname', 'id']);
            }
        ])->where('slug', $slug)->where('language', 'tr')->first();
        if ($post) {
            $post->title = stripslashes($post->title);
            $post->content = stripslashes($post->content);
        }
        return response()->json($post);
    }

    public function sliderPosts($language){
        return response()->json(Posts::with([
            'user' => function($query){
                $query->select(['nickname', 'id']);
            },
            'postMedia',
            'categories'
        ])->where('language', $language)->where('is_published', 1)->where('post_type', 'post')->orderBy('created_at', 'desc')->paginate(5));
    }
}
