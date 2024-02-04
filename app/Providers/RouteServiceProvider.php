<?php

namespace App\Providers;

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
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/settings')
                ->group(base_path('routes/panel/settings/settings.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/routes')
                ->group(base_path('routes/panel/routes.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/menu')
                ->group(base_path('routes/panel/menu.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/ip-filter')
                ->group(base_path('routes/panel/ip-filter.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/profile')
                ->group(base_path('routes/panel/profile.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/users')
                ->group(base_path('routes/panel/users.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/notes')
                ->group(base_path('routes/panel/notes.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/blogs/comments')
                ->group(base_path('routes/panel/blogs/comments.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/blogs/categories')
                ->group(base_path('routes/panel/blogs/categories.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/{type}')
                ->group(base_path('routes/panel/page-post.php'));

            Route::middleware(['web', 'auth'])
                ->prefix('panel/{type}/history')
                ->group(base_path('routes/panel/history.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
