<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'userList'])
    ->can('admin', 'App\Models\User')
    ->name('admin.users');

Route::get('/create', [UserController::class, 'create'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.create');

Route::post('/create', [UserController::class, 'store'])
    ->can('admin', 'App\Models\User');

Route::post('/delete', [UserController::class, 'userDelete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.delete');

// NOTE: intentionally NOT gated by can('admin'). During impersonation the active
// session IS the (possibly low-privilege) impersonated user; gating this with
// 'admin' would trap that session and prevent restoring the original identity.
// secretLogout() is a safe no-op unless 'impersonated_original' is set in session.
Route::get('/secret-logout', [UserController::class, 'secretLogout'])
    ->name('admin.user.secret-logout');

Route::post('/user/session/kill', [UserController::class, 'killSession'])
    ->can('ownAdmin', 'App\Models\User')
    ->name('user.session.logout');

Route::post('/user/session/kill-all', [UserController::class, 'killAllSession'])
    ->can('ownAdmin', 'App\Models\User')
    ->name('user.session.logout-all');

Route::post('/user/profile-image', [UserController::class, 'profileImage'])
    ->can('ownAdmin', 'App\Models\User')
    ->name('admin.user.profile-image');

Route::post('/user/delete-profile-image', [UserController::class, 'deleteProfilImage'])
    ->can('ownAdmin', 'App\Models\User')
    ->name('admin.user.profile-image-delete');

Route::get('/{user_id}/secret-login', [UserController::class, 'userSecretLogin'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.secret-login');

Route::get('/{user_id}/edit', [UserController::class, 'userEdit'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.edit');

Route::post('/{user_id}/edit', [UserController::class, 'userUpdate'])
    ->can('admin', 'App\Models\User');

Route::post('/{user_id}/social', [UserController::class, 'userSocialSave'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.social.save');

Route::post('/{user_id}/password', [UserController::class, 'userPasswordChange'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.password');

Route::post('/{user_id}/webauthn', [UserController::class, 'webauthnList'])
    ->can('admin', 'App\Models\WebAuthnCredential')
    ->name('admin.user.webauthn');

Route::post('/{user_id}/webauthn/delete', [UserController::class, 'webauthnDelete'])
    ->can('admin', 'App\Models\WebAuthnCredential')
    ->name('admin.user.webauthn.delete');

Route::post('/{user_id}/webauthn/rename', [UserController::class, 'webauthnRename'])
    ->can('admin', 'App\Models\WebAuthnCredential')
    ->name('admin.user.webauthn.rename');

Route::post('/{user_id}/email-change', [UserController::class, 'userEmailChange'])
    ->can('admin', 'App\Models\User')
    ->name('admin.user.email');
