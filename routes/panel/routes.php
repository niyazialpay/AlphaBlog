<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\Admin\RouteRedirectsController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('adminRoutes');

Route::post('/save/{route?}', [App\Http\Controllers\Admin\RouteRedirectsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('adminRouteSave');

Route::post('/delete', [App\Http\Controllers\Admin\RouteRedirectsController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('adminRoutesDelete');

Route::post('/{route?}', [App\Http\Controllers\Admin\RouteRedirectsController::class, 'show'])
    ->can('admin', 'App\Models\User')
    ->name('adminRoutesShow');
