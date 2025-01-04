<?php

use Illuminate\Support\Facades\Route;

Route::get('/',
    [App\Http\Controllers\Admin\FirewallController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.firewall');

Route::get('/logs',
    [App\Http\Controllers\Admin\FirewallController::class, 'logs'])
    ->can('admin', 'App\Models\User')
    ->name('admin.firewall.logs');
