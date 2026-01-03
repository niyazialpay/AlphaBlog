<?php

use Illuminate\Support\Facades\Route;

Route::post('/think/delete-all', [App\Http\Controllers\Admin\SearchController::class, 'deleteAll'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.delete.all');

Route::post('/think/delete-not-interested', [App\Http\Controllers\Admin\SearchController::class, 'deleteNotThink'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.delete.not-interested');

Route::post('/think/delete/{search?}', [App\Http\Controllers\Admin\SearchController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.delete');

Route::post('/think/{search?}', [App\Http\Controllers\Admin\SearchController::class, 'think'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.think');

Route::get('/', [App\Http\Controllers\Admin\SearchController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.index');

Route::post('/', [App\Http\Controllers\Admin\SearchController::class, 'check'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.check');

Route::post('/general', [App\Http\Controllers\Admin\SearchController::class, 'search'])->name('general.search');
