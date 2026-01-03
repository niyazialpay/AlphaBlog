<?php

namespace App\Providers;

use App\Actions\LanguageAction;
use App\Models\Post\Categories;
use App\Models\Post\Posts;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::bind('showPost', function ($slug) {
            return Posts::with(['user', 'user.social', 'categories', 'comments' => function ($query) {
                return $query->where('is_approved', 1);
            }, 'comments.user'])
                ->where('slug', $slug)
                ->where('language', request()->segment(1))
                ->where('is_published', 1)->firstOrFail();
        });

        Route::bind('showCategory', function ($slug) {
            return Categories::where('slug', $slug)
                ->where('language', request()->segment(1))->firstOrFail();
        });

        Route::bind('showTag', function ($tag) {
            return Posts::search($tag)
                ->query(function ($query) {
                    $query->with(['user', 'categories', 'comments', 'comments.user']);
                })
                ->where('post_type', 'post')
                ->where('language', session('language'))
                ->where('is_published', 1)
                ->orderBy('created_at', 'desc')
                ->paginate(10)->withQueryString();
        });

        Route::bind('showUserPosts', function ($nickname) {
            return User::select(['name', 'surname', 'nickname', 'email'])
                ->with(['posts', 'posts.categories', 'posts.media', 'posts.media.model'])
                ->whereHas('posts', function ($query) {
                    $query->where('language', request()->segment(1))
                        ->where('is_published', 1);
                })
                ->where('nickname', $nickname)
                ->paginate(10)->withQueryString();
        });

    }
}
