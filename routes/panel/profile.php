<?php

use App\Http\Controllers\Admin\PushSubscriptionController;
use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\WebAuthn\WebAuthnController;
use App\Http\Controllers\WebAuthn\WebAuthnRegisterController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.index');

Route::post('/save', [UserController::class, 'save'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('admin.profile.save');

Route::post('/webauthn', [WebAuthnController::class, 'WebAuthnList'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('user.security.webauthn');

Route::post('/webauthn/delete', [WebAuthnController::class, 'delete'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('user.security.webauthn.delete');

Route::post('/webauthn/rename', [WebAuthnController::class, 'rename'])
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('user.security.webauthn.rename');

Route::post('/webauthn/register/options', [WebAuthnRegisterController::class, 'options'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('webauthn.register.options');

Route::post('/webauthn/register', [WebAuthnRegisterController::class, 'register'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->can('own', 'App\Models\WebAuthnCredential')
    ->name('webauthn.register');

Route::post('/password/change', [UserController::class, 'changePassword'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.password');

Route::post('/social-save', [UserController::class, 'socialSave'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.social.save');

Route::post('/2fa-confirm', [TwoFactorAuthController::class, 'confirm'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.confirm');

Route::post('/2fa-enable', [TwoFactorAuthController::class, 'store'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.enable');

Route::delete('/2fa-disable', [TwoFactorAuthController::class, 'destroy'])
    ->can('own', 'App\Models\User')
    ->name('two-factor.disable');

Route::post('/email-change', [UserController::class, 'changeEmail'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.email');

Route::post('/privacy', [UserController::class, 'privacy'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.privacy');

/*
 * Web Push.
 *
 * Abonelik uclari VERI ucudur: tarayicinin PushManager degerlerini kaydeder /
 * siler ve JSON doner (istemci axios ile cagirir). Tercih kaydi ise profil
 * ekraninin form eylemidir ve yonlendirir.
 */
Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.push.subscribe');

Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.push.unsubscribe');

Route::post('/notifications/preferences', [PushSubscriptionController::class, 'preferences'])
    ->can('own', 'App\Models\User')
    ->name('admin.profile.notifications.preferences');
