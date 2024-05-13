<?php

namespace App\Providers;

use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Observers\PostsObserver;
use App\Policies\CommentPolicy;
use App\Policies\PersonalNoteCategoryPolicy;
use App\Policies\PersonalNotesPolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFile;
use Opcodes\LogViewer\LogFolder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(PersonalNotes::class, PersonalNotesPolicy::class);
        Gate::policy(PersonalNoteCategories::class, PersonalNoteCategoryPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(\Laragear\WebAuthn\Models\WebAuthnCredential::class, UserPolicy::class);
        Gate::policy(\App\Models\WebAuthnCredential::class, UserPolicy::class);
        Gate::policy(Posts::class, PostPolicy::class);
        Gate::policy(Comments::class, CommentPolicy::class);
        Gate::policy(Categories::class, PostPolicy::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        Posts::observe(PostsObserver::class);

        Gate::define('viewPulse', function (User $user) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        LogViewer::auth(function ($request) {
            return $request->user()?->role === 'owner' || $request->user()?->role === 'admin';
        });

        Gate::define('downloadLogFile', function (?User $user, LogFile $file) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        Gate::define('downloadLogFolder', function (?User $user, LogFolder $folder) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        Gate::define('deleteLogFile', function (?User $user, LogFile $file) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        Gate::define('deleteLogFolder', function (?User $user, LogFolder $folder) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->role === 'owner' || $user->role === 'admin';
        });

        Pulse::user(fn ($user) => [
            'name' => $user->name.' '.$user->surname,
            'extra' => $user->email,
            'avatar' => 'https://gravatar.com/avatar/'.md5($user->email).'?d=mp',
        ]);
    }
}
