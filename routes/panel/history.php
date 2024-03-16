<?php
use Illuminate\Support\Facades\Route;

Route::get('/{posts}', [App\Http\Controllers\Admin\Post\HistoryController::class, 'history'])
    ->can('view', 'posts')
    ->name('admin.post.history');

Route::get('/{posts}/{history}', [App\Http\Controllers\Admin\Post\HistoryController::class, 'show'])
    ->can('view', 'posts')
    ->name('admin.post.history.show');

Route::post('/{posts}/{history}/delete', [App\Http\Controllers\Admin\Post\HistoryController::class, 'delete'])
    ->can('delete', 'posts')
    ->name('admin.post.history.delete');

Route::post('/{posts}/{history}/revert', [App\Http\Controllers\Admin\Post\HistoryController::class, 'revert'])
    ->can('revert', 'posts')
    ->name('admin.post.history.revert');
