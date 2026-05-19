<?php

use App\Http\Controllers\Admin\LanguagesController;
use App\Http\Controllers\Admin\Settings\AdvertiseSettingsController;
use App\Http\Controllers\Admin\Settings\AnalyticsSettingsController;
use App\Http\Controllers\Admin\Settings\GeneralSettingsController;
use App\Http\Controllers\Admin\Settings\NotificationsController;
use App\Http\Controllers\Admin\Settings\SeoSettingsController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Settings\SocialSettingsController;
use App\Http\Controllers\Admin\Settings\ThemesSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SettingsController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings');

Route::post('/seo/save', [SeoSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.save');

Route::post('/seo/robots/save', [SeoSettingsController::class, 'saveRobots'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.robots.save');

Route::post('/seo/llms/save', [SeoSettingsController::class, 'saveLlms'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.llms.save');

Route::post('/seo/llms/clear-cache', [SeoSettingsController::class, 'clearLlmsCache'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.llms.clear-cache');

Route::post('/seo/google-indexing/save', [SeoSettingsController::class, 'saveGoogleIndexing'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.google-indexing.save');

Route::post('/general/save', [GeneralSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.save');

Route::post('/general/logo/delete/{type}',
    [GeneralSettingsController::class, 'deleteLogo'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.logo.delete')->where('type', 'light|dark');

Route::post('/general/favicon/delete',
    [GeneralSettingsController::class, 'deleteFavicon'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.favicon.delete');

Route::post('/general/app_icon/delete',
    [GeneralSettingsController::class, 'deleteAppIcon'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.app_icon.delete');

Route::post('/social/save', [SocialSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.social.save');

Route::post('/social/header/save', [SocialSettingsController::class, 'saveHeader'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.social.header.save');

Route::post('/analytics/save', [AnalyticsSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.analytics.save');

Route::post('/advertise/save', [AdvertiseSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.advertisement.save');

Route::post('/languages/show', [LanguagesController::class, 'show'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.languages.show');

Route::post('/languages/save/{language?}', [LanguagesController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.languages.save');

Route::post('/languages/delete', [LanguagesController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.languages.delete');

Route::post('/themes/delete', [ThemesSettingsController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.themes.delete');

Route::post('/theme/upload', [ThemesSettingsController::class, 'upload'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.themes.upload');

Route::get('/theme/activate/{theme}',
    [ThemesSettingsController::class, 'makeDefault'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.themes.default');

Route::post('/notification/save', [NotificationsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.notifications.save');
