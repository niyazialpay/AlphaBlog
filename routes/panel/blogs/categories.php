<?php

use Illuminate\Support\Facades\Route;

Route::post('/delete', [App\Http\Controllers\Admin\Post\CategoryController::class, 'delete'])
    ->can('category', 'App\Models\Post\Categories')
    ->name('admin.categories.delete');

Route::post('/delete-image', [App\Http\Controllers\Admin\Post\CategoryController::class, 'deleteImage'])
    ->can('category', 'App\Models\Post\Categories')
    ->name('admin.categories.image.delete');

Route::get('/{category?}', [App\Http\Controllers\Admin\Post\CategoryController::class, 'index'])
    ->can('category', 'App\Models\Post\Categories')
    ->name('admin.categories');

Route::post('/{category?}', [App\Http\Controllers\Admin\Post\CategoryController::class, 'store'])
    ->can('category', 'App\Models\Post\Categories');
