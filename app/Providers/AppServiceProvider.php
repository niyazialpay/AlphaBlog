<?php

namespace App\Providers;

use App\Models\Post\Posts;
use App\Observers\PostsObserver;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        $loader = AliasLoader::getInstance();

        $loader->alias(\Laravel\Sanctum\PersonalAccessToken::class,
            \App\Models\PersonalAccessToken::class);

        Posts::observe(PostsObserver::class);
    }
}
