<?php

use Illuminate\Support\Facades\Route;

Route::post('/editor/image/upload/{note?}',
    [App\Http\Controllers\Admin\PersonalNotesController::class, 'editorImageUpload'])
    ->can('create', 'App\Models\PersonalNotes\PersonalNotes')
    ->name('admin.notes.editor.image.upload');

Route::post('/{note}/image/delete', [App\Http\Controllers\Admin\PersonalNotesController::class, 'postImageDelete'])
    ->can('own', 'note')
    ->name('admin.notes.media.delete');

Route::get('/', [App\Http\Controllers\Admin\PersonalNotesController::class, 'index'])
    ->name('admin.notes');

Route::get('/create', [App\Http\Controllers\Admin\PersonalNotesController::class, 'create'])
    ->name('admin.notes.create');

Route::get('/show/{note}/edit', [App\Http\Controllers\Admin\PersonalNotesController::class, 'create'])
    ->can('own', 'note')
    ->name('admin.notes.edit');

Route::get('/show/{note}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'show'])
    ->can('own', 'note')
    ->name('admin.notes.show');

Route::post('/save', [App\Http\Controllers\Admin\PersonalNotesController::class, 'save'])
    ->can('create', 'App\Models\PersonalNotes')
    ->name('admin.notes.save');

Route::post('/save/{note}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'save'])
    ->can('own', 'note')
    ->name('admin.notes.edit.save');

Route::get('/{note}/media', [App\Http\Controllers\Admin\PersonalNotesController::class, 'media'])
    ->can('own', 'note')
    ->name('admin.notes.media');

Route::post('/delete/{note}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'delete'])
    ->can('own', 'note')
    ->name('admin.notes.delete');

Route::post('/encryption', [App\Http\Controllers\Admin\PersonalNotesController::class, 'encryption'])
    ->name('admin.notes.encryption');

Route::post('/categories', [App\Http\Controllers\Admin\PersonalNotesController::class, 'categorySave'])
    ->can('create', 'App\Models\PersonalNotes\PersonalNoteCategories')
    ->name('admin.notes.categories.create');

Route::post('/categories/{category}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'categorySave'])
    ->can('own', 'category')
    ->name('admin.notes.categories.update');

Route::get('/categories/{category}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'categories'])
    ->can('own', 'category')
    ->name('admin.notes.category');

Route::post('/categories/delete/{category?}',
    [App\Http\Controllers\Admin\PersonalNotesController::class, 'categoryDelete'])
    ->can('own', 'category')
    ->name('admin.notes.categories.delete');

Route::get('/categories', [App\Http\Controllers\Admin\PersonalNotesController::class, 'categories'])
    ->name('admin.notes.categories');
