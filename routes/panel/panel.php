<?php

use Illuminate\Support\Facades\Route;

//admin panel
Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->name('admin.index');

Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])
    ->name('admin.logout');

Route::get('/lock-screen', [\App\Http\Controllers\Admin\TwoFactorAuthController::class, 'lock'])->name('lockscreen');

Route::get('/contact', [App\Http\Controllers\Admin\ContactController::class, 'index'])
    ->name('admin.contact_page')->can('admin', 'App\Models\User');

Route::post('/contact', [App\Http\Controllers\Admin\ContactController::class, 'save'])
    ->can('admin', 'App\Models\User');

Route::get('/change-language/{language}', [App\Http\Controllers\Admin\DashboardController::class, 'changeLanguage'])
    ->name('admin.change_language');

Route::get('/clear-cache', [App\Http\Controllers\Admin\CacheController::class, 'clearCache'])
    ->name('admin.clear_cache');

Route::get('/ai-chatbot', [App\Http\Controllers\StreamingChatController::class, 'index'])->name('chatbot');
Route::get('/ai-chatbot/Gemini', [App\Http\Controllers\StreamingChatController::class, 'Gemini'])->name('chatbot.Gemini');
Route::get('/ai-chatbot/ChatGPT', [App\Http\Controllers\StreamingChatController::class, 'ChatGPT'])->name('chatbot.ChatGPT');

Route::get('/monitoring/pulse', [App\Http\Controllers\Admin\MonitoringController::class, 'showPulse'])
    ->name('admin.monitoring.pulse')->can('viewPulse');

Route::get('/monitoring/telescope', [App\Http\Controllers\Admin\MonitoringController::class, 'showTelescope'])
    ->name('admin.monitoring.telescope')->can('viewTelescope');

Route::get('/monitoring/horizon', [App\Http\Controllers\Admin\MonitoringController::class, 'showHorizon'])
    ->name('admin.monitoring.horizon')->can('viewHorizon');

Route::get('/monitoring/logs', [App\Http\Controllers\Admin\MonitoringController::class, 'showLogs'])
    ->name('admin.monitoring.logs')->can('viewPulse');
