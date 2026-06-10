<?php

use App\Http\Controllers\Admin\SearchController;
use Illuminate\Support\Facades\Route;

Route::post('/think/delete-all', [SearchController::class, 'deleteAll'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.delete.all');

Route::post('/think/delete-not-interested', [SearchController::class, 'deleteNotThink'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.delete.not-interested');

Route::post('/think/delete/{search?}', [SearchController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.delete');

Route::post('/think/{search?}', [SearchController::class, 'think'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.think');

Route::get('/', [SearchController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.index');

Route::post('/', [SearchController::class, 'check'])
    ->can('admin', 'App\Models\User')
    ->name('admin.search.check');

Route::post('/general', [SearchController::class, 'search'])
    ->can('admin', 'App\Models\User')
    ->name('general.search');
