<?php

use App\Http\Controllers\Auth\WebAuthn\WebAuthnLoginController;
use App\Http\Controllers\Auth\WebAuthn\WebAuthnRegisterController;
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

        Route::group(['prefix' => 'routes'], function(){
            Route::get('/', [App\Http\Controllers\Admin\RoutesController::class, 'index'])->name('adminRoutes');

            Route::post('/save/{route?}', [App\Http\Controllers\Admin\RoutesController::class, 'save'])->name('adminRouteSave');
            Route::post('/delete', [App\Http\Controllers\Admin\RoutesController::class, 'delete'])->name('adminRoutesDelete');

            Route::post('/{route?}', [App\Http\Controllers\Admin\RoutesController::class, 'show'])->name('adminRoutesShow');
        });

        Route::group(['prefix' => 'ip-filter'], function(){
            Route::post('/delete',
                [App\Http\Controllers\Admin\IPFilterController::class, 'delete'])
                ->name('admin.ip-filter.delete');

            Route::get('/',
                [App\Http\Controllers\Admin\IPFilterController::class, 'index'])
                ->name('admin.ip-filter');

            Route::get('/create',
                [App\Http\Controllers\Admin\IPFilterController::class, 'show'])
                ->name('admin.ip-filter.create');

            Route::get('{ip_filter}',
                [App\Http\Controllers\Admin\IPFilterController::class, 'show'])
                ->name('admin.ip-filter.show');

            Route::post('/save/{ip_filter?}',
                [App\Http\Controllers\Admin\IPFilterController::class, 'save'])
                ->name('admin.ip-filter.save');
        });


        Route::group(['prefix' => 'profile'], function(){
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])
                ->name('admin.profile.index');

            Route::post('/save', [App\Http\Controllers\Admin\UserController::class, 'save'])
                ->name('admin.profile.save');

            Route::post('/password/change', [App\Http\Controllers\Admin\UserController::class, 'changePassword'])
                ->name('admin.profile.password');

            Route::post('/webauthn', [App\Http\Controllers\Admin\WebAuthnController::class, 'list'])->name('user.security.webauthn');
            Route::post('/webauthn/delete', [App\Http\Controllers\Admin\WebAuthnController::class, 'delete'])->name('user.security.webauthn.delete');
            Route::post('/webauthn/rename', [App\Http\Controllers\Admin\WebAuthnController::class, 'rename'])->name('user.security.webauthn.rename');
        });

        Route::group(['prefix' => 'settings'], function(){
            Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings');
            Route::get('/create', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])->name('admin.settings.create');
            Route::post('/create', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])->name('admin.settings.create');
            Route::get('/{settings}', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])->name('admin.settings.show');
            Route::get('/{settings}/edit', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])->name('admin.settings.edit');
            Route::patch('/{settings}/edit', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])->name('admin.settings.edit');
            Route::delete('/{settings}/delete', [App\Http\Controllers\Admin\SettingsController::class, 'settings'])->name('admin.settings.delete');
        });

        Route::group(['prefix' => 'blogs'], function(){
            Route::group(['prefix' => '/comments'], function(){
                Route::get('/', [App\Http\Controllers\Admin\Post\CommentController::class, 'index'])
                    ->name('admin.post.comments');
                Route::post('/{comment}/delete', [App\Http\Controllers\Admin\Post\CommentController::class, 'delete'])
                    ->name('admin.post.comments.delete');
                Route::post('/{comment}/restore', [App\Http\Controllers\Admin\Post\CommentController::class, 'restore'])
                    ->name('admin.post.comments.restore')->withTrashed();
                Route::post('/{comment}/delete/permanent', [App\Http\Controllers\Admin\Post\CommentController::class, 'forceDelete'])
                    ->name('admin.post.comments.force-delete')->withTrashed();
                Route::post('/{comment}/edit', [App\Http\Controllers\Admin\Post\CommentController::class, 'edit'])
                    ->name('admin.post.comments.edit');
                Route::post('/{comment}/approve', [App\Http\Controllers\Admin\Post\CommentController::class, 'approve'])
                    ->name('admin.post.comments.approve');
                Route::post('/{comment}/disapprove', [App\Http\Controllers\Admin\Post\CommentController::class, 'disapprove'])
                    ->name('admin.post.comments.disapprove');

                Route::post('/save/{comment?}', [App\Http\Controllers\Admin\Post\CommentController::class, 'save'])
                    ->name('admin.post.comments.save');
            });

            Route::group(['prefix' => 'categories'], function(){
                Route::post('/delete', [App\Http\Controllers\Admin\Post\CategoryController::class, 'delete'])
                    ->name('admin.categories.delete');

                Route::get('/{category?}', [App\Http\Controllers\Admin\Post\CategoryController::class, 'index'])
                    ->name('admin.categories');

                Route::post('/{category?}', [App\Http\Controllers\Admin\Post\CategoryController::class, 'store']);
            });
        });

        Route::group(['prefix' => '/{type}'], function(){
            Route::get('/', [App\Http\Controllers\Admin\Post\PostController::class, 'index'])
                ->name('admin.posts');

            Route::get('/create', [App\Http\Controllers\Admin\Post\PostController::class, 'create'])
                ->name('admin.post.create');

            Route::post('/save/{post?}', [App\Http\Controllers\Admin\Post\PostController::class, 'save'])
                ->name('admin.post.save')->middleware('merge_post_type');

            Route::get('/{post}/media', [App\Http\Controllers\Admin\Post\PostController::class, 'media'])
                ->name('admin.post.media');

            Route::get('/{post}/edit', [App\Http\Controllers\Admin\Post\PostController::class, 'create'])
                ->name('admin.post.edit')->middleware('merge_post_type');

            Route::post('/{post}/delete', [App\Http\Controllers\Admin\Post\PostController::class, 'delete'])
                ->name('admin.post.delete');

            Route::post('/{post}/delete/permanent', [App\Http\Controllers\Admin\Post\PostController::class, 'forceDelete'])
                ->name('admin.post.delete.permanent')->withTrashed();

            Route::post('/{post}/restore', [App\Http\Controllers\Admin\Post\PostController::class, 'restore'])
                ->name('admin.post.restore')->withTrashed();


            Route::post('/image/delete/{post?}', [App\Http\Controllers\Admin\Post\PostController::class, 'imageDelete'])
                ->name('admin.post.image.delete');

            Route::get('/{category}', [App\Http\Controllers\Admin\Post\PostController::class, 'index'])
                ->name('admin.post.category');

            Route::post('/editor/image/upload/{post?}', [App\Http\Controllers\ImageController::class, 'editorImageUpload'])
                ->name('admin.post.editor.image.upload');

            Route::post('/editor/image/delete/{post}', [App\Http\Controllers\ImageController::class, 'postImageDelete'])
                ->name('admin.post.media.delete');

            Route::group(['prefix' => 'history'], function(){
                Route::get('/{posts}', [App\Http\Controllers\Admin\Post\HistoryController::class, 'history'])
                    ->name('admin.post.history');

                Route::get('/{posts}/{history}', [App\Http\Controllers\Admin\Post\HistoryController::class, 'show'])
                    ->name('admin.post.history.show');

                Route::post('/{posts}/{history}/delete', [App\Http\Controllers\Admin\Post\HistoryController::class, 'delete'])
                    ->name('admin.post.history.delete');

                Route::post('/{posts}/{history}/revert', [App\Http\Controllers\Admin\Post\HistoryController::class, 'revert'])
                    ->name('admin.post.history.revert');
            });

        });

        Route::group(['prefix' => 'notes'], function(){
            Route::get('/notes', [App\Http\Controllers\Admin\PersonalNotes::class, 'index'])->name('admin.notes');
        });

        /*
        Route::group(['prefix' => 'users'], function(){
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users.create');
            Route::post('/create', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users.create');
            Route::get('/{users}', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users.show');
            Route::get('/{users}/edit', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users.edit');
            Route::patch('/{users}/edit', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users.edit');
            Route::delete('/{users}/delete', [App\Http\Controllers\Admin\UserController::class, 'users'])->name('admin.users.delete');
        });

        Route::group(['prefix' => 'settings'], function(){
            Route::get('/', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings');
            Route::get('/create', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings.create');
            Route::post('/create', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings.create');
            Route::get('/{settings}', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings.show');
            Route::get('/{settings}/edit', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings.edit');
            Route::patch('/{settings}/edit', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings.edit');
            Route::delete('/{settings}/delete', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings.delete');
        });

        Route::group(['prefix' => 'menus'], function(){
            Route::get('/', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus');
            Route::get('/create', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus.create');
            Route::post('/create', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus.create');
            Route::get('/{menus}', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus.show');
            Route::get('/{menus}/edit', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus.edit');
            Route::patch('/{menus}/edit', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus.edit');
            Route::delete('/{menus}/delete', [App\Http\Controllers\AdminController::class, 'menus'])->name('admin.menus.delete');
        });

        Route::group(['prefix' => 'menu-items'], function(){
            Route::get('/', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items');
            Route::get('/create', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items.create');
            Route::post('/create', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items.create');
            Route::get('/{menuItems}', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items.show');
            Route::get('/{menuItems}/edit', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items.edit');
            Route::patch('/{menuItems}/edit', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items.edit');
            Route::delete('/{menuItems}/delete', [App\Http\Controllers\AdminController::class, 'menuItems'])->name('admin.menu-items.delete');
        });*/
    });
});


Route::get('/'.__('categories.page_url').'/{categories:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('post.categories');
Route::get('/'.__('categories.page_url').'/{categories:slug}/{posts:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('post.categories');
Route::get('/'.__('categories.page_url'), [App\Http\Controllers\HomeController::class, 'index'])->name('categories');

Route::get('/'.__('tags.tags_url').'/{tags:tags}', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/{post:slug}', [App\Http\Controllers\HomeController::class, 'index'])->name('page');

