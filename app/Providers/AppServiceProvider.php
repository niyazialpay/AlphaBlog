<?php

namespace App\Providers;

use App\Models\ContactMessages;
use App\Models\PersonalNotes\PersonalNoteCategories;
use App\Models\PersonalNotes\PersonalNotes;
use App\Models\Post\Categories;
use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\User;
use App\Models\WebAuthnCredential;
use App\Observers\ContactMessagesObserver;
use App\Observers\MediaObserver;
use App\Observers\PostsObserver;
use App\Observers\UserObserver;
use App\Policies\CommentPolicy;
use App\Policies\PersonalNoteCategoryPolicy;
use App\Policies\PersonalNotesPolicy;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Telescope\TelescopeServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\LogFile;
use Opcodes\LogViewer\LogFolder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        Gate::policy(WebAuthnCredential::class, UserPolicy::class);
        Gate::policy(Posts::class, PostPolicy::class);
        Gate::policy(Comments::class, CommentPolicy::class);
        Gate::policy(Categories::class, PostPolicy::class);

        $this->app->register(TelescopeServiceProvider::class);
        $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
        $this->app->register(HorizonServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        if ($this->shouldForceHttps()) {
            URL::forceScheme('https');
        }

        Paginator::currentPathResolver(function () {
            return Request::getPathInfo(); // sadece "/tr/kategoriler/php"
        });

        Posts::observe(PostsObserver::class);
        User::observe(UserObserver::class);
        ContactMessages::observe(ContactMessagesObserver::class);

        Media::observe(MediaObserver::class);

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

        // SECURITY: do not surface raw email as PII in Pulse dashboards. Use the
        // public nickname/name and a hashed gravatar id only.
        Pulse::user(fn ($user) => [
            'name' => $user->nickname ?: trim($user->name.' '.$user->surname),
            'extra' => '#'.$user->id,
            'avatar' => 'https://gravatar.com/avatar/'.hash('sha256', $user->email).'?d=mp',
        ]);
    }

    private function shouldForceHttps(): bool
    {
        // Use config only (never env() outside config/): env() returns null under
        // config:cache, silently disabling HTTPS forcing in production.
        if (filter_var(config('app.force_https'), FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        return str_starts_with((string) config('app.url'), 'https://');
    }
}
