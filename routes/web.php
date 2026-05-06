<?php

use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthorsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageProcessController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostQrController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SiteMap\RssController;
use App\Http\Controllers\SiteMap\SitemapController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAuthn\WebAuthnLoginController;
use App\Http\Middleware\AdminOneSignal;
use App\Http\Middleware\CloudflareTurnstile;
use App\Http\Middleware\NewCommentsCount;
use App\Http\Middleware\SearchedWords;
use App\Http\Middleware\VerifyOTP;
use App\Models\Languages;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
    $domain = config('app.cdn_url');
} else {
    $domain = config('app.url');
}
Route::domain($domain)->group(function () {
    Route::get('/image/{path}/{width}/{height}/{type}/{image}', [ImageProcessController::class, 'index'])
        ->name('image')
        ->where([
            'path' => '[a-zA-Z0-9\/]+',
            'image' => '[a-zA-Z0-9\/\.\-_]+',
            'width' => '[0-9]+',
            'height' => '[0-9]+',
            'type' => '[a-zA-Z0-9\/]+',
        ]);

});

Route::domain(config('app.url'))->group(function () {
    Route::any('/'.config('settings.admin_panel_path').'/manifest.json',
        [ManifestController::class, 'manifestPanel'])
        ->name('manifest.panel');

    Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
        ->middleware('auth')
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', __('auth.verification_sent'));
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::group([
        'middleware' => [
            NewCommentsCount::class,
            AdminOneSignal::class,
            SearchedWords::class,
            VerifyOTP::class,
            EnsureEmailIsVerified::class,
        ],
    ], function () {

        Route::middleware(['web', 'auth'])
            ->prefix(config('settings.admin_panel_path'))
            ->group(base_path('routes/panel/panel.php'));

        Route::middleware(['web', 'auth'])
            ->prefix(config('settings.admin_panel_path').'/notifications')
            ->group(base_path('routes/panel/notifications.php'));

        Route::middleware(['web', 'auth'])
            ->prefix(config('settings.admin_panel_path').'/search')
            ->group(base_path('routes/panel/search.php'));

        Route::middleware(['web', 'auth'])
            ->prefix(config('settings.admin_panel_path').'/settings')
            ->group(base_path('routes/panel/settings/settings.php'));

        Route::middleware(['web', 'auth'])
            ->prefix(config('settings.admin_panel_path').'/cloudflare')
            ->group(base_path('routes/panel/cloudflare.php'));

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
            ->prefix(config('settings.admin_panel_path').'/firewall')
            ->group(base_path('routes/panel/firewall.php'));

        Route::middleware(['web', 'auth'])
            ->prefix(config('settings.admin_panel_path').'/system-logs')
            ->group(base_path('routes/panel/logs.php'));

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
    });

    Route::get('/login',
        [\App\Http\Controllers\Admin\UserController::class, 'login'])
        ->name('login');

    Route::post('/login/first',
        [LoginController::class, 'loginFirst'])->name('login.first_step')->middleware([
            ProtectAgainstSpam::class,
            'throttle:3,1',
        ]);

    Route::post('/login',
        [LoginController::class, 'login'])->middleware([
            ProtectAgainstSpam::class,
            'throttle:3,1',
            CloudflareTurnstile::class,
        ]);

    Route::post('/login/2fa-verify', [TwoFactorAuthController::class, 'verify'])
        ->middleware([
            ProtectAgainstSpam::class,
        ])
        ->name('two-factor.verify');

    Route::get('/forgot-password',
        [ResetPasswordController::class, 'forgotPassword'])
        ->name('forgot-password')->middleware('guest');

    Route::post('/forgot-password',
        [ResetPasswordController::class, 'resetPassword'])
        ->middleware([
            'guest',
            ProtectAgainstSpam::class,
            CloudflareTurnstile::class,
        ]);

    Route::get('/reset-password/{token}',
        [LoginController::class, 'showResetForm'])
        ->middleware('guest')->name('password.reset');

    Route::post('/reset-password',
        [LoginController::class, 'reset'])
        ->middleware([
            'guest',
            ProtectAgainstSpam::class,
            CloudflareTurnstile::class,
        ])->name('password.update');

    Route::post('/webauthn/login/options',
        [WebAuthnLoginController::class, 'options'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webauthn.login.options');

    Route::post('/webauthn/login',
        [WebAuthnLoginController::class, 'login'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webauthn.login');

    Route::group([], function () {
        try {
            $languages = Languages::all();
        } catch (Exception $e) {
            $languages = collect();
        }

        Route::get('/sitemap.xml', [SitemapController::class, 'index']);

        Route::get('/sitemap', [SitemapController::class, 'index'])
            ->name('sitemap');

        Route::any('/manifest.json', [ManifestController::class, 'manifest'])
            ->name('manifest');

        Route::prefix('/{language}')
            ->whereIn('language', $languages->pluck('code')->toArray())
            ->group(function () use ($languages) {
                App::setLocale(session('language'));

                foreach ($languages as $language) {
                    foreach (Lang::get('routes', locale: $language->code) as $k => $v) {
                        Route::pattern($k, $v);
                    }
                }

                Route::get('/rss', [RssController::class, 'show'])
                    ->name('rss');

                Route::get('/sitemap-categories', [SitemapController::class, 'categories'])
                    ->name('sitemap.categories');

                Route::get('/sitemap-posts', [SitemapController::class, 'posts'])
                    ->name('sitemap.posts');

                Route::get('/sitemap-users', [SitemapController::class, 'users'])
                    ->name('sitemap.users');

                Route::post('/comment-save', [CommentController::class, 'store'])
                    ->middleware([
                        // \App\Http\Middleware\CloudflareTurnstile::class,
                        ProtectAgainstSpam::class,
                    ])
                    ->name('comment.save');

                Route::get('/{tags}/{showTag:tag}', [TagController::class, 'show'])
                    ->name('post.tags')
                    ->whereIn('tags', Lang::get('route_tags'));

                Route::get('/{categories}/{showCategory:slug}', [CategoryController::class, 'show'])
                    ->name('post.categories')
                    ->whereIn('categories', Lang::get('route_categories'));

                Route::get('/{user}/{users:nickname}', [UserController::class, 'posts'])
                    ->name('user.posts')
                    ->whereIn('user', Lang::get('route_user'));

                Route::get('/{archives}/{year}/{month?}/{day?}', [ArchiveController::class, 'show'])
                    ->name('post.archives')
                    ->whereIn('archives', Lang::get('route_archives'));

                Route::get('/{search_result}/{search_term?}', [SearchController::class, 'index'])
                    ->name('search.result')
                    ->whereIn('search_result', Lang::get('route_search'));

                Route::get('/{contact}', [ContactController::class, 'index'])
                    ->name('contact.front')
                    ->whereIn('contact', Lang::get('route_contact'));

                Route::get('/{authors}', [AuthorsController::class, 'index'])
                    ->name('post.authors')
                    ->whereIn('authors', Lang::get('route_authors'));

                Route::post('/{contact}', [ContactController::class, 'send'])
                    ->name('contact.send')
                    ->whereIn('contact', Lang::get('route_contact'))
                    ->middleware([
                        CloudflareTurnstile::class,
                        ProtectAgainstSpam::class,
                    ]);

                Route::post('/{contact}/ajax', [ContactController::class, 'send_ajax'])
                    ->name('contact.send-ajax')
                    ->whereIn('contact', Lang::get('route_contact'))
                    ->middleware([
                        CloudflareTurnstile::class,
                        ProtectAgainstSpam::class,
                    ]);

                Route::get('/{showPost:slug}/qr/{qr_key}', [PostQrController::class, 'redirect'])
                    ->name('post.qr');

                Route::get('/{showPost:slug}', [PostController::class, 'show'])->name('page');
            });

        Route::get('/{language?}', [HomeController::class, 'index'])
            ->name('home')
            ->whereIn('language', $languages->pluck('code')->toArray());
    });
});
