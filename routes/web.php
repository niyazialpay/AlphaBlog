<?php

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

Route::any('/corbado/webhook', [App\Http\Controllers\Admin\WebAuthnController::class, 'webhook'])
    ->name('corbado.webhook');

Route::any('/corbado/redirect',  [App\Http\Controllers\Admin\WebAuthnController::class, 'redirect']);

Route::get('/login',
    [App\Http\Controllers\Admin\UserController::class, 'login'])
    ->name('login');

Route::post('/login',
    [App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware([
    'honeypot',
    'throttle:login',
    'cloudflare_turnstile'
]);

Route::get('/forgot-password',
    [App\Http\Controllers\Auth\LoginController::class, 'forgotPassword'])
    ->name('forgot-password')->middleware('guest');

Route::post('/forgot-password',
    [App\Http\Controllers\Auth\LoginController::class, 'resetPassword'])
    ->middleware([
        'guest',
        'honeypot',
        'cloudflare_turnstile'
    ]);

Route::get('/reset-password/{token}',
    [App\Http\Controllers\Auth\LoginController::class, 'showResetForm'])
    ->middleware('guest')->name('password.reset');

Route::post('/reset-password',
    [App\Http\Controllers\Auth\LoginController::class, 'reset'])
    ->middleware([
        'guest',
        'honeypot',
        'cloudflare_turnstile'
    ])->name('password.update');

Route::get('/image/{path}/{width}/{height}/{type}/{image}',
    [App\Http\Controllers\ImageProcessController::class, 'index'])
    ->name('image')
    ->where([
        'path' => '[a-zA-Z0-9\/]+',
        'image' => '[a-zA-Z0-9\/\.\-_]+',
        'width' => '[0-9]+',
        'height' => '[0-9]+',
        'type' => '[a-zA-Z0-9\/]+'
    ]);

Route::get('/sitemap', [App\Http\Controllers\SiteMap\SitemapController::class, 'index'])
    ->name('sitemap');

Route::any('/manifest.json', [App\Http\Controllers\ManifestController::class, 'manifest'])->name('manifest');

Route::group(['prefix' => '/{language}'], function () {
    App::setLocale(session()->get('language'));

    foreach(Lang::get('routes') as $k => $v) {
        Route::pattern($k, $v);
    }

    Route::get('/rss', [App\Http\Controllers\SiteMap\RssController::class, 'show'])
        ->name('rss');

    Route::get('/sitemap-categories', [App\Http\Controllers\SiteMap\SitemapController::class, 'categories'])
        ->name('sitemap.categories');

    Route::get('/sitemap-posts', [App\Http\Controllers\SiteMap\SitemapController::class, 'posts'])
        ->name('sitemap.posts');

    Route::get('/sitemap-users', [App\Http\Controllers\SiteMap\SitemapController::class, 'users'])
        ->name('sitemap.users');

    Route::post('/comment-save', [App\Http\Controllers\CommentController::class, 'store'])
        ->middleware(['cloudflare_turnstile', 'honeypot'])
        ->name('comment.save');

     Route::get('/{tags}/{showTag:tag}', [App\Http\Controllers\TagController::class, 'show'])
        ->name('post.tags')
        ->where('tags', Lang::get('routes.tags'));

    Route::get('/{categories}/{showCategory:slug}', [App\Http\Controllers\CategoryController::class, 'show'])
        ->name('post.categories')
        ->where('categories', Lang::get('routes.categories'));

    Route::get('/{user}/{users:nickname}', [App\Http\Controllers\UserController::class, 'posts'])
        ->name('user.posts')
        ->where('user', Lang::get('routes.user'));

    Route::get('/{archives}/{year}/{month?}/{day?}', [App\Http\Controllers\ArchiveController::class, 'show'])
        ->name('post.archives')
        ->where('archives', Lang::get('routes.archives'));

    Route::get('/{search_result}/{search_term?}', [App\Http\Controllers\SearchController::class, 'index'])
        ->name('search.result')
        ->where('search_result', Lang::get('routes.search_result'));

    Route::get('/{contact}', [App\Http\Controllers\ContactController::class, 'index'])
        ->name('contact.front')
        ->where('contact', Lang::get('routes.contact'));

    Route::post('/{contact}', [App\Http\Controllers\ContactController::class, 'send'])
        ->name('contact.send')
        ->where('contact', Lang::get('routes.contact'))
        ->middleware(['cloudflare_turnstile', 'honeypot']);

    Route::post('/{contact}/ajax', [App\Http\Controllers\ContactController::class, 'send_ajax'])
        ->name('contact.send-ajax')
        ->where('contact', Lang::get('routes.contact'))
        ->middleware(['cloudflare_turnstile', 'honeypot']);

    Route::get('/{showPost:slug}', [App\Http\Controllers\PostController::class, 'show'])->name('page');
});

Route::get('/{language?}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


