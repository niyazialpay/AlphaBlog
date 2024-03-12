<?php
#admin panel
Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->name('admin.index');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('logout');

Route::get('/contact', [App\Http\Controllers\Admin\ContactController::class, 'index'])
    ->name('admin.contact_page')->can('admin', 'App\Models\User');

Route::post('/contact', [App\Http\Controllers\Admin\ContactController::class, 'save'])
    ->can('admin', 'App\Models\User');
