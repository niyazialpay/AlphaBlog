<?php

use Illuminate\Support\Facades\Route;

//admin panel
Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->name('admin.index');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('admin.logout');

Route::get('/contact', [App\Http\Controllers\Admin\ContactController::class, 'index'])
    ->name('admin.contact_page')->can('admin', 'App\Models\User');

Route::post('/contact', [App\Http\Controllers\Admin\ContactController::class, 'save'])
    ->can('admin', 'App\Models\User');

Route::get('/change-language/{language}', [App\Http\Controllers\Admin\DashboardController::class, 'changeLanguage'])
    ->name('admin.change_language');
