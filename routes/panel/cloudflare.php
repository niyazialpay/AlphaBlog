<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/'], function () {});

Route::get('/', [\App\Http\Controllers\Admin\Cloudflare\CloudflareController::class, 'index'])
    ->can('cloudflare', 'App\Models\User')
    ->name('cf.dashboard');

Route::post('/cache-clear', [\App\Http\Controllers\Admin\Cloudflare\CloudflareController::class, 'CacheClear'])
    ->name('admin.cloudflare.cache.clear')
    ->can('cloudflare', 'App\Models\User');

Route::post('/toggle-development', [\App\Http\Controllers\Admin\Cloudflare\CloudflareController::class, 'ToggleDevelopment'])
    ->name('admin.cloudflare.toggle.development')
    ->can('cloudflare', 'App\Models\User');

Route::group(['prefix' => '/dns'], function () {
    Route::get('/', [\App\Http\Controllers\Admin\Cloudflare\DNSController::class, 'index'])
        ->can('cloudflare', 'App\Models\User')
        ->name('cf.dns');
    Route::post('/', [\App\Http\Controllers\Admin\Cloudflare\DNSController::class, 'dns_json'])->can('owner', 'App\Models\User');
    Route::post('/save', [\App\Http\Controllers\Admin\Cloudflare\DNSController::class, 'create_edit'])
        ->can('cloudflare', 'App\Models\User')
        ->name('cf.dns.save');
    Route::post('/delete', [\App\Http\Controllers\Admin\Cloudflare\DNSController::class, 'delete'])
        ->can('cloudflare', 'App\Models\User')
        ->name('cf.dns.delete');
});

Route::group(['prefix' => 'settings'], function () {
    Route::post('/cloudflare', [App\Http\Controllers\Admin\Settings\SettingsController::class, 'updateApiSettings'])
        ->can('cloudflare', 'App\Models\User')
        ->name('cf.update.api.settings');
});
