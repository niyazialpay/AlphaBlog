<?php
Route::get('/', [\App\Http\Controllers\Admin\Settings\SettingsController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings');

Route::post('/seo/save', [App\Http\Controllers\Admin\Settings\SeoSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.save');

Route::post('/seo/robots/save', [App\Http\Controllers\Admin\Settings\SeoSettingsController::class, 'saveRobots'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.robots.save');

Route::post('/general/save', [App\Http\Controllers\Admin\Settings\GeneralSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.save');

Route::post('/general/logo/delete/{type}',
    [App\Http\Controllers\Admin\Settings\GeneralSettingsController::class, 'deleteLogo'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.logo.delete')->where('type', 'light|dark');

Route::post('/general/favicon/delete',
    [App\Http\Controllers\Admin\Settings\GeneralSettingsController::class, 'deleteFavicon'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.favicon.delete');

Route::post('/general/app_icon/delete',
    [App\Http\Controllers\Admin\Settings\GeneralSettingsController::class, 'deleteAppIcon'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.general.app_icon.delete');

Route::post('/social/save', [App\Http\Controllers\Admin\Settings\SocialSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.social.save');

Route::post('/social/header/save', [App\Http\Controllers\Admin\Settings\SocialSettingsController::class, 'saveHeader'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.social.header.save');

Route::post('/analytics/save', [App\Http\Controllers\Admin\Settings\AnalyticsSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.analytics.save');

Route::post('/advertise/save', [App\Http\Controllers\Admin\Settings\AdvertiseSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.advertisement.save');

Route::post('/languages/show', [App\Http\Controllers\Admin\LanguagesController::class, 'show'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.languages.show');

Route::post('/languages/save/{language?}', [App\Http\Controllers\Admin\LanguagesController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.languages.save');

Route::post('/languages/delete', [App\Http\Controllers\Admin\LanguagesController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.languages.delete');

Route::post('/themes/delete', [App\Http\Controllers\Admin\Settings\ThemesSettingsController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.themes.delete');

Route::post('/theme/upload' , [App\Http\Controllers\Admin\Settings\ThemesSettingsController::class, 'upload'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.themes.upload');

Route::get('/theme/activate/{theme}', [App\Http\Controllers\Admin\Settings\ThemesSettingsController::class, 'makeDefault'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.themes.default');
