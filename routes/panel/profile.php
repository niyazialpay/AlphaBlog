<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.index');

Route::post('/save', [App\Http\Controllers\Admin\UserController::class, 'save'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('admin.profile.save');

Route::post('/webauthn', [App\Http\Controllers\WebAuthn\WebAuthnController::class, 'WebAuthnList'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('user.security.webauthn');

Route::post('/webauthn/delete', [App\Http\Controllers\WebAuthn\WebAuthnController::class, 'delete'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('user.security.webauthn.delete');

Route::post('/webauthn/rename', [App\Http\Controllers\WebAuthn\WebAuthnController::class, 'rename'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('user.security.webauthn.rename');

Route::post('/webauthn/register/options', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'options'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('webauthn.register.options');

Route::post('/webauthn/register', [\App\Http\Controllers\WebAuthn\WebAuthnRegisterController::class, 'register'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('webauthn.register');

Route::post('/password/change', [App\Http\Controllers\Admin\UserController::class, 'changePassword'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.password');

Route::post('/social-save', [App\Http\Controllers\Admin\UserController::class, 'socialSave'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.social.save');

Route::post('/2fa-confirm', [App\Http\Controllers\Admin\TwoFactorAuthController::class, 'confirm'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.confirm');

Route::post('/2fa-enable', [App\Http\Controllers\Admin\TwoFactorAuthController::class, 'store'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.enable');

Route::delete('/2fa-disable', [App\Http\Controllers\Admin\TwoFactorAuthController::class, 'destroy'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.disable');
