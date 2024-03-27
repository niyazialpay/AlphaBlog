<?php

use App\Models\Languages;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'middleware' => [
        \App\Http\Middleware\NewCommentsCount::class,
        \App\Http\Middleware\AdminOneSignal::class
    ]
], function () {
    Route::any('/'.config('settings.admin_panel_path').'/manifest.json',
        [\App\Http\Controllers\ManifestController::class, 'manifestPanel'])
        ->name('manifest.panel');

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
});

Route::any('/corbado/webhook', [\App\Http\Controllers\Admin\WebAuthnController::class, 'webhook'])
    ->name('corbado.webhook');

Route::any('/corbado/redirect',  [\App\Http\Controllers\Admin\WebAuthnController::class, 'redirect']);

Route::get('/login',
    [\App\Http\Controllers\Admin\UserController::class, 'login'])
    ->name('login');

Route::post('/login',
    [\App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware([
    \Spatie\Honeypot\ProtectAgainstSpam::class,
    //'throttle:login',
    \App\Http\Middleware\CloudflareTurnstile::class
]);

Route::get('/forgot-password',
    [\App\Http\Controllers\Auth\LoginController::class, 'forgotPassword'])
    ->name('forgot-password')->middleware('guest');

Route::post('/forgot-password',
    [\App\Http\Controllers\Auth\LoginController::class, 'resetPassword'])
    ->middleware([
        'guest',
        'honeypot',
        'cloudflare_turnstile'
    ]);

Route::get('/reset-password/{token}',
    [\App\Http\Controllers\Auth\LoginController::class, 'showResetForm'])
    ->middleware('guest')->name('password.reset');

Route::post('/reset-password',
    [\App\Http\Controllers\Auth\LoginController::class, 'reset'])
    ->middleware([
        'guest',
        'honeypot',
        'cloudflare_turnstile'
    ])->name('password.update');

Route::get('/image/{path}/{width}/{height}/{type}/{image}',
    [\App\Http\Controllers\ImageProcessController::class, 'index'])
    ->name('image')
    ->where([
        'path' => '[a-zA-Z0-9\/]+',
        'image' => '[a-zA-Z0-9\/\.\-_]+',
        'width' => '[0-9]+',
        'height' => '[0-9]+',
        'type' => '[a-zA-Z0-9\/]+'
    ]);

Route::get('/sitemap.xml', [\App\Http\Controllers\SiteMap\SitemapController::class, 'index']);

Route::get('/sitemap', [\App\Http\Controllers\SiteMap\SitemapController::class, 'index'])
    ->name('sitemap');


Route::any('/manifest.json', [\App\Http\Controllers\ManifestController::class, 'manifest'])
    ->name('manifest');

$languages = Languages::all();

Route::group(['prefix' => '/{language}'], function () use($languages) {
    App::setLocale(session('language'));

    foreach ($languages as $language) {
        foreach(Lang::get('routes', locale:$language->code) as $k => $v) {
            Route::pattern($k, $v);
        }
    }

    Route::get('/rss', [\App\Http\Controllers\SiteMap\RssController::class, 'show'])
        ->name('rss');

    Route::get('/sitemap-categories', [\App\Http\Controllers\SiteMap\SitemapController::class, 'categories'])
        ->name('sitemap.categories');

    Route::get('/sitemap-posts', [\App\Http\Controllers\SiteMap\SitemapController::class, 'posts'])
        ->name('sitemap.posts');

    Route::get('/sitemap-users', [\App\Http\Controllers\SiteMap\SitemapController::class, 'users'])
        ->name('sitemap.users');

    Route::post('/comment-save', [\App\Http\Controllers\CommentController::class, 'store'])
        ->middleware([
            \App\Http\Middleware\CloudflareTurnstile::class,
            \Spatie\Honeypot\ProtectAgainstSpam::class
        ])
        ->name('comment.save');

    Route::get('/{tags}/{showTag:tag}', [\App\Http\Controllers\TagController::class, 'show'])
        ->name('post.tags')
        ->whereIn('tags', Lang::get('route_tags'));

    Route::get('/{categories}/{showCategory:slug}', [\App\Http\Controllers\CategoryController::class, 'show'])
        ->name('post.categories')
        ->whereIn('categories', Lang::get('route_categories'));

    Route::get('/{user}/{users:nickname}', [\App\Http\Controllers\UserController::class, 'posts'])
        ->name('user.posts')
        ->whereIn('user', Lang::get('route_user'));

    Route::get('/{archives}/{year}/{month?}/{day?}', [\App\Http\Controllers\ArchiveController::class, 'show'])
        ->name('post.archives')
        ->whereIn('archives', Lang::get('route_archives'));

    Route::get('/{search_result}/{search_term?}', [\App\Http\Controllers\SearchController::class, 'index'])
        ->name('search.result')
        ->whereIn('search_result', Lang::get('route_search'));

    Route::get('/{contact}', [\App\Http\Controllers\ContactController::class, 'index'])
        ->name('contact.front')
        ->whereIn('contact', Lang::get('route_contact'));

    Route::post('/{contact}', [\App\Http\Controllers\ContactController::class, 'send'])
        ->name('contact.send')
        ->whereIn('contact', Lang::get('route_contact'))
        ->middleware(['cloudflare_turnstile', 'honeypot']);

    Route::post('/{contact}/ajax', [\App\Http\Controllers\ContactController::class, 'send_ajax'])
        ->name('contact.send-ajax')
        ->whereIn('contact', Lang::get('route_contact'))
        ->middleware(['cloudflare_turnstile', 'honeypot']);

    Route::get('/{showPost:slug}', [\App\Http\Controllers\PostController::class, 'show'])->name('page');
})->whereIn('language', $languages->pluck('code')->toArray());

Route::get('/{language?}', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');


