<?php

use App\Models\PostHistory;
use App\Models\Posts;
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


#admin panel
Route::group(['prefix' => '/'.config('settings.admin_panel_path')], function(){

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

    Route::group(['middleware' => 'auth'], function(){
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('admin.index');

        Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

        Route::group(['prefix' => 'settings'], function(){
            Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings');

            Route::get('/create', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings.create');

            Route::post('/create', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings.create');

            Route::get('/{settings}', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings.show');

            Route::get('/{settings}/edit', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings.edit');

            Route::patch('/{settings}/edit', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings.edit');

            Route::delete('/{settings}/delete', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])
                ->can('admin', 'App\Models\User')
                ->name('admin.settings.delete');
        });

    });
});


Route::get('/'.__('categories.page_url').'/{categories:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('post.categories');
Route::get('/'.__('categories.page_url').'/{categories:slug}/{posts:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('post.categories');
Route::get('/'.__('categories.page_url'), [App\Http\Controllers\HomeController::class, 'index'])->name('categories');

Route::get('/'.__('tags.tags_url').'/{tags:tags}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/{post:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('page');

