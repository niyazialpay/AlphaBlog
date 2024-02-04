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

Route::any('/corbado/webhook', [App\Http\Controllers\Admin\WebAuthnController::class, 'webhook'])->name('corbado.webhook');
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


#admin panel
Route::group(['prefix' => '/'.config('settings.admin_panel_path')], function(){


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

    Route::group(['middleware' => 'auth'], function(){
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('admin.index');

        Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
            ->name('logout');
    });
});


Route::get('/'.__('categories.page_url').'/{categories:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('post.categories');
Route::get('/'.__('categories.page_url').'/{categories:slug}/{posts:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('post.categories');
Route::get('/'.__('categories.page_url'), [App\Http\Controllers\HomeController::class, 'index'])->name('categories');

Route::get('/'.__('tags.tags_url').'/{tags:tags}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/{post:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('page');

