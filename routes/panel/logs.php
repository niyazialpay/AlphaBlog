<?php

use Illuminate\Support\Facades\Route;

Route::get('/',
    [App\Http\Controllers\Admin\LogsController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.system-logs');

Route::post('/',
    [App\Http\Controllers\Admin\LogsController::class, 'logsData'])
    ->can('admin', 'App\Models\User')
    ->name('admin.system-logs.data');
