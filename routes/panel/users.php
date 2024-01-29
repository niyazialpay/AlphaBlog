<?php
Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'userList'])
    ->can('admin', 'App\Models\User')
    ->name('admin.users');

Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.create');

Route::post('/create', [App\Http\Controllers\Admin\UserController::class, 'store'])
    ->can('admin', 'App\Models\User');

Route::post('/delete', [App\Http\Controllers\Admin\UserController::class, 'userDelete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.delete');

Route::get('/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'userEdit'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.edit');

Route::post('/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'userUpdate'])
    ->can('admin', 'App\Models\User');

Route::post('/{user}/social', [App\Http\Controllers\Admin\UserController::class, 'userSocialSave'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.social.save');
