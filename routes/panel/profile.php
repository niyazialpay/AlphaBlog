<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.index');

Route::post('/save', [App\Http\Controllers\Admin\UserController::class, 'save'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.save');

Route::post('/password/change', [App\Http\Controllers\Admin\UserController::class, 'changePassword'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.password');

Route::post('/webauthn', [App\Http\Controllers\Admin\WebAuthnController::class, 'list'])
    ->can('own', 'App\Models\User')
    ->name('user.security.webauthn');
Route::post('/webauthn/delete', [App\Http\Controllers\Admin\WebAuthnController::class, 'delete'])
    ->can('own', 'App\Models\User')
    ->name('user.security.webauthn.delete');

Route::post('/webauthn/rename', [App\Http\Controllers\Admin\WebAuthnController::class, 'rename'])
    ->can('own', 'App\Models\User')
    ->name('user.security.webauthn.rename');

Route::post('/social-save', [App\Http\Controllers\Admin\UserController::class, 'socialSave'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.social.save');

Route::post('/2fa-confirm', [App\Http\Controllers\Admin\TwoFactorAuthController::class, 'confirm'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.confirm');

Route::delete('/2fa-disable', [App\Http\Controllers\Admin\TwoFactorAuthController::class, 'destroy'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.disable');
