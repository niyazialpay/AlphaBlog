<?php

namespace App\Providers;

use App\Action\LanguageAction;
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
    public const string HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        LanguageAction::setLanguage(request());
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Route::bind('showPost', function ($slug) {
            return Posts::with(['user', 'categories', 'comments', 'comments.user'])
                ->where('slug', $slug)
                ->where('language', session()->get('language'))
                ->where('is_published', true)->firstOrFail();
        });

        Route::bind('showCategory', function ($slug) {
            return Categories::where('slug', $slug)
                ->where('language', session()->get('language'))->firstOrFail();
        });

        Route::bind('showTag', function ($tag) {
            return Posts::search($tag)
                ->query(function($query){
                    $query->with(['user', 'categories', 'comments', 'comments.user']);
                })
                ->where('language', session()->get('language'))
                ->where('is_published', true)->paginate(10);
        });

        Route::bind('showUserPosts', function ($nickname) {
            return User::select(['name', 'surname', 'nickname', 'email'])
                ->with(['posts', 'posts.categories', 'posts.media', 'posts.media.model'])
                ->whereHas('posts', function($query){
                    $query->where('language', session()->get('language'))
                        ->where('is_published', true);
                })
                ->where('nickname', $nickname)->paginate();
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path'))
                ->group(base_path('routes/panel/panel.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/settings')
                ->group(base_path('routes/panel/settings/settings.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/routes')
                ->group(base_path('routes/panel/routes.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/menu')
                ->group(base_path('routes/panel/menu.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/ip-filter')
                ->group(base_path('routes/panel/ip-filter.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/profile')
                ->group(base_path('routes/panel/profile.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/users')
                ->group(base_path('routes/panel/users.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/notes')
                ->group(base_path('routes/panel/notes.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/blogs/comments')
                ->group(base_path('routes/panel/blogs/comments.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/blogs/categories')
                ->group(base_path('routes/panel/blogs/categories.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/{type}')
                ->group(base_path('routes/panel/page-post.php'));

            Route::middleware(['web', 'auth'])
                ->prefix(config('settings.admin_panel_path').'/{type}/history')
                ->group(base_path('routes/panel/history.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

        });
    }
}
