<?php

use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::delete('/delete', [NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::get('/read-and-redirect/{id}', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');
