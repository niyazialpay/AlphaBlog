<?php

namespace App\Providers;

use App\Models\PersonalNotes;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\PersonalNotesPolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PersonalNotes::class    =>  PersonalNotesPolicy::class,
        User::class             =>  UserPolicy::class,
        Posts::class            =>  PostPolicy::class,
        Comments::class         =>  CommentPolicy::class,
        Categories::class       =>  PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
