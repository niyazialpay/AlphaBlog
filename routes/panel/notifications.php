<?php

use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
Route::get('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::delete('/delete', [NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::get('/read-and-redirect/{id}', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');
Route::get('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
Route::delete('/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.destroyAll');

/*
|--------------------------------------------------------------------------
| POST alias'lari
|--------------------------------------------------------------------------
|
| Yukaridaki iki GET route'u DURUM DEGISTIRIYOR. Inertia v2'de <Link prefetch>
| hover'da gercek bir GET atar; bu da farkinda olmadan bildirimleri okundu
| isaretlerdi. Vue tarafi bu POST alias'larini kullanir.
|
| Eski GET route'lari oldugu gibi birakildi: eski Blade ekrani ve olasi
| yer imleri kirilmasin.
*/
Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead.post');
Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead.post');
