<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/{user_id}/edit', [App\Http\Controllers\Admin\UserController::class, 'userEdit'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.edit');

Route::post('/{user_id}/edit', [App\Http\Controllers\Admin\UserController::class, 'userUpdate'])
    ->can('admin', 'App\Models\User');

Route::post('/{user_id}/social', [App\Http\Controllers\Admin\UserController::class, 'userSocialSave'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.social.save');

Route::post('/{user_id}/password', [App\Http\Controllers\Admin\UserController::class, 'userPasswordChange'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.password');

Route::post('/{user_id}/webauthn', [App\Http\Controllers\Admin\UserController::class, 'webauthnList'])
    ->can('admin', 'App\Models\WebAuthnCredential')
    ->name('admin.user.webauthn');

Route::post('/{user_id}/webauthn/delete', [App\Http\Controllers\Admin\UserController::class, 'webauthnDelete'])
    ->can('admin', 'App\Models\WebAuthnCredential')
    ->name('admin.user.webauthn.delete');

Route::post('/{user_id}/webauthn/rename', [App\Http\Controllers\Admin\UserController::class, 'webauthnRename'])
    ->can('admin', 'App\Models\WebAuthnCredential')
    ->name('admin.user.webauthn.rename');

Route::post('/{user_id}/email-change', [App\Http\Controllers\Admin\UserController::class, 'userEmailChange'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.email');
