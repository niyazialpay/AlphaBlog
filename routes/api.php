<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
    $domain = config('app.cdn_url');
} else {
    $domain = config('app.url');
}
Route::domain($domain)->group(function () {
    Route::get('/image/{path}/{width}/{height}/{type}/{image}', [\App\Http\Controllers\ImageProcessController::class, 'index'])
        ->name('image')
        ->where([
            'path' => '[a-zA-Z0-9\/]+',
            'image' => '[a-zA-Z0-9\/\.\-_]+',
            'width' => '[0-9]+',
            'height' => '[0-9]+',
            'type' => '[a-zA-Z0-9\/]+',
        ]);

    Route::get('/{any}', [\App\Http\Controllers\CDNController::class, 'index'])
        ->where('any', '.*')->name('cdn');

    if (config('app.cdn_url') != null && config('app.cdn_url') != config('app.url')) {
        Route::get('/', function () {
            return redirect(config('app.url'));
        });
    }
});

Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/{language}/categories', [\App\Http\Controllers\Api\Frontend\CategoryController::class, 'index']);
Route::get('/{language}/top-categories', [\App\Http\Controllers\Api\Frontend\CategoryController::class, 'topCategories']);

Route::get('/{language}/slider-posts', [\App\Http\Controllers\Api\Frontend\PostController::class, 'sliderPosts']);

Route::get('/{language}/categories/{id}', [\App\Http\Controllers\Api\Frontend\CategoryController::class, 'show']);

Route::post('/{language}/posts', [\App\Http\Controllers\Api\Frontend\PostController::class, 'index']);

Route::get('/{language}/posts/{id}', [\App\Http\Controllers\Api\Frontend\PostController::class, 'show']);

Route::get('/{language}/general-settings', [\App\Http\Controllers\Api\Frontend\SettingController::class, 'generalSettings']);

Route::get('/{language}/menus', [\App\Http\Controllers\Api\Frontend\MenuController::class, 'menu']);
