<?php

use Illuminate\Support\Facades\Route;

Route::post('/delete',
    [App\Http\Controllers\Admin\IPFilterController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.ip-filter.delete');

Route::get('/',
    [App\Http\Controllers\Admin\IPFilterController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.ip-filter');

Route::get('/create',
    [App\Http\Controllers\Admin\IPFilterController::class, 'show'])
    ->can('admin', 'App\Models\User')
    ->name('admin.ip-filter.create');

Route::get('{ip_filter}',
    [App\Http\Controllers\Admin\IPFilterController::class, 'show'])
    ->can('admin', 'App\Models\User')
    ->name('admin.ip-filter.show');

Route::post('/save/{ip_filter?}',
    [App\Http\Controllers\Admin\IPFilterController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.ip-filter.save');
Route::post('/toggle/status',
    [App\Http\Controllers\Admin\IPFilterController::class, 'toggleStatus'])
    ->can('admin', 'App\Models\User')
    ->name('admin.ip-filter.toggle-status');
