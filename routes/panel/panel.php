<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\AiChatbotController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactMessagesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\SearchConsoleController;
use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// admin panel
Route::get('/', [DashboardController::class, 'index'])
    ->name('admin.index');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('admin.logout');

Route::get('/lock-screen', [TwoFactorAuthController::class, 'lock'])->name('lockscreen');

Route::get('/contact/messages', [ContactMessagesController::class, 'index'])
    ->name('admin.contact_messages')->can('admin', 'App\Models\User');

Route::get('/contact', [ContactController::class, 'index'])
    ->name('admin.contact_page')->can('admin', 'App\Models\User');

Route::post('/contact', [ContactController::class, 'save'])
    ->can('admin', 'App\Models\User');

Route::get('/change-language/{language}', [DashboardController::class, 'changeLanguage'])
    ->name('admin.change_language');

Route::get('/clear-cache', [CacheController::class, 'clearCache'])
    ->can('admin', 'App\Models\User')
    ->name('admin.clear_cache');

Route::get('/ai-chatbot', [AiChatbotController::class, 'index'])->can('admin', 'App\Models\User')->name('chatbot');
Route::get('/ai-chatbot/conversations', [AiChatbotController::class, 'conversations'])->can('admin', 'App\Models\User')->name('chatbot.conversations');
Route::get('/ai-chatbot/conversations/{conversationId}', [AiChatbotController::class, 'conversation'])->can('admin', 'App\Models\User')->name('chatbot.conversation')->whereUuid('conversationId');
Route::post('/ai-chatbot/messages', [AiChatbotController::class, 'message'])->can('admin', 'App\Models\User')->middleware('throttle:30,1')->name('chatbot.message');

Route::get('/monitoring/pulse', [MonitoringController::class, 'showPulse'])
    ->name('admin.monitoring.pulse')->can('viewPulse');

Route::get('/monitoring/telescope', [MonitoringController::class, 'showTelescope'])
    ->name('admin.monitoring.telescope')->can('viewTelescope');

Route::get('/monitoring/horizon', [MonitoringController::class, 'showHorizon'])
    ->name('admin.monitoring.horizon')->can('viewHorizon');

Route::get('/monitoring/logs', [MonitoringController::class, 'showLogs'])
    ->name('admin.monitoring.logs')->can('viewPulse');

Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->name('admin.analytics')->can('admin', 'App\Models\User');

Route::post('/analytics', [AnalyticsController::class, 'fetchAnalytics'])
    ->name('admin.analytics.fetch')->can('admin', 'App\Models\User');

Route::get('/search-console', [SearchConsoleController::class, 'index'])
    ->name('admin.search-console')->can('admin', 'App\Models\User');

Route::post('/search-console/fetch', [SearchConsoleController::class, 'fetch'])
    ->name('admin.search-console.fetch')->can('admin', 'App\Models\User');

Route::post('/dashboard/widgets', [DashboardController::class, 'saveWidgets'])
    ->name('admin.dashboard.widgets.save')->can('admin', 'App\Models\User');

Route::get('/about', [AboutController::class, 'index'])
    ->name('admin.about')->can('admin', 'App\Models\User');
