<?php

namespace App\Providers;

use App\Models\PersonalNotes;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Observers\PostsObserver;
use App\Policies\CommentPolicy;
use App\Policies\PersonalNotesPolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(PersonalNotes::class, PersonalNotesPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Posts::class, PostPolicy::class);
        Gate::policy(Comments::class, CommentPolicy::class);
        Gate::policy(Categories::class, PostPolicy::class);
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
