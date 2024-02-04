<?php
Route::get('/{menu?}', [\App\Http\Controllers\Admin\Menu\MenuController::class, 'index'])
    ->can('admin', 'App\Models\User')
    ->name('admin.menu.index');

Route::post('/save/{menu?}', [\App\Http\Controllers\Admin\Menu\MenuController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.menu.save');

Route::get('/show/{menu}', [\App\Http\Controllers\Admin\Menu\MenuItemsController::class, 'show'])
    ->can('admin', 'App\Models\User')
    ->name('admin.menu.show');

Route::post('/delete', [\App\Http\Controllers\Admin\Menu\MenuController::class, 'delete'])
    ->can('admin', 'App\Models\User')
    ->name('admin.menu.delete');

Route::post('/menu-item/save', [\App\Http\Controllers\Admin\Menu\MenuItemsController::class, 'save'])
    ->can('admin', 'App\Models\User')
    ->name('admin.menu-item.save');
