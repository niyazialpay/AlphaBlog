<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
