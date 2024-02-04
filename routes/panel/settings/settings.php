<?php
Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings');

Route::post('/seo/save', [App\Http\Controllers\Admin\Settings\SeoSettingsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.save');

Route::post('/seo/robots/save', [App\Http\Controllers\Admin\Settings\SeoSettingsController::class, 'saveRobots'])
    ->can('admin', 'App\Models\User')
    ->name('admin.settings.seo.robots.save');
