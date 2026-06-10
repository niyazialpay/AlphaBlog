<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Frontend\CategoryController;
use App\Http\Controllers\Api\Frontend\MenuController;
use App\Http\Controllers\Api\Frontend\PostController;
use App\Http\Controllers\Api\Frontend\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/{language}/categories', [CategoryController::class, 'index']);
Route::get('/{language}/top-categories', [CategoryController::class, 'topCategories']);

Route::get('/{language}/slider-posts', [PostController::class, 'sliderPosts']);

Route::get('/{language}/categories/{id}', [CategoryController::class, 'show']);

Route::post('/{language}/posts', [PostController::class, 'index']);

Route::get('/{language}/posts/{id}', [PostController::class, 'show']);

Route::get('/{language}/general-settings', [SettingController::class, 'generalSettings']);

Route::get('/{language}/menus', [MenuController::class, 'menu']);
