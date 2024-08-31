<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post\Posts;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index($language, Request $request)
    {
        if ($request->has('search')) {
            $posts = Posts::search($request->search)
                ->query(function ($query) {
                    $query->with([
                        'categories' => function ($query) {
                            $query->select(['name', 'slug']);
                        },
                        'user' => function ($query) {
                            $query->select(['nickname', 'id']);
                        },
                        'postMedia',
                    ]);
                    $query->where('posts.created_at', '<=', now()->format('Y-m-d H:i:s'));
                })
                ->where('language', $language)->where('is_published', true)->where('post_type', 'post')->orderBy('created_at', 'desc');
        } else {
            $posts = Posts::with([
                'categories' => function ($query) {
                    $query->select(['name', 'slug']);
                },
                'user' => function ($query) {
                    $query->select(['nickname', 'id', 'name', 'surname', 'email']);
                },
                'postMedia',
            ])->select(['title', 'slug', 'posts.id', 'user_id', 'created_at'])
                ->where('posts.created_at', '<=', now()->format('Y-m-d H:i:s'))
                ->where('language', $language)->where('is_published', true)->where('post_type', 'post')->orderBy('created_at', 'desc');

            if ($request->has('category')) {
                $posts = $posts->whereHas('categories', function ($query) use ($request) {
                    $query->where('category_id', $request->category);
                });
            }
        }

        $posts = $posts->paginate(30);

        $posts->getCollection()->transform(function ($post) {
            $post->title = stripslashes($post->title);
            $post->content = stripslashes($post->content);

            return $post;
        });

        // Manually append the profile_image attribute to the user models
        foreach ($posts as $post) {
            if ($post->user) {
                $post->user->makeHidden('email'); // email alanını gizli tutuyoruz
            }
        }

        return response()->json($posts);
    }

    public function show($language, $id)
    {
        $post = Posts::with([
            'categories' => function ($query) {
                $query->select(['name', 'slug']);
            },
            'user' => function ($query) {
                $query->select(['nickname', 'id', 'name', 'surname', 'email']);
            },
            'postMedia',
            'comments' => function ($query) {
                $query->where('is_approved', 1);
            },
        ])->where('id', $id)->where('language', $language)->where('is_published', 1)->firstOrFail();
        if ($post) {
            $post->title = stripslashes($post->title);
            $post->content = stripslashes($post->content);
            $post->user->makeHidden('email');
        }

        return response()->json($post);
    }

    public function sliderPosts($language)
    {
        $posts = Posts::with([
            'user' => function ($query) {
                $query->select(['nickname', 'id', 'name', 'surname', 'email']);
            },
            'postMedia',
            'categories',
        ])->select('title', 'id', 'user_id', 'slug', 'created_at')
            ->where('language', $language)->where('is_published', 1)
            ->where('post_type', 'post')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        foreach ($posts as $post) {
            if ($post->user) {
                $post->user->makeHidden('email'); // email alanını gizli tutuyoruz
            }
        }

        return response()->json($posts);
    }
}
