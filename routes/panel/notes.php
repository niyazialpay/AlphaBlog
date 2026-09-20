<?php

use App\Http\Controllers\Admin\PersonalNotesController;
use Illuminate\Support\Facades\Route;

Route::post('/editor/image/upload',
    [PersonalNotesController::class, 'editorImageUpload'])
    ->can('create', 'App\Models\PersonalNotes\PersonalNotes')
    ->name('admin.notes.editor.image.upload');

Route::post('/editor/image/upload/{note}',
    [PersonalNotesController::class, 'editorImageUpload'])
    ->can('own', 'note');

Route::post('/{note}/image/delete', [PersonalNotesController::class, 'postImageDelete'])
    ->can('own', 'note')
    ->name('admin.notes.media.delete');

Route::get('/', [PersonalNotesController::class, 'index'])
    ->name('admin.notes');

Route::get('/create', [PersonalNotesController::class, 'create'])
    ->name('admin.notes.create');

Route::get('/show/{note}/edit', [PersonalNotesController::class, 'create'])
    ->can('own', 'note')
    ->name('admin.notes.edit');

Route::get('/show/{note}', [PersonalNotesController::class, 'show'])
    ->can('own', 'note')
    ->name('admin.notes.show');

Route::post('/save', [PersonalNotesController::class, 'save'])
    ->can('create', 'App\Models\PersonalNotes\PersonalNotes')
    ->name('admin.notes.save');

Route::post('/save/{note}', [PersonalNotesController::class, 'save'])
    ->can('own', 'note')
    ->name('admin.notes.edit.save');

Route::get('/{note}/media', [PersonalNotesController::class, 'media'])
    ->can('own', 'note')
    ->name('admin.notes.media');

Route::post('/delete/{note}', [PersonalNotesController::class, 'delete'])
    ->can('own', 'note')
    ->name('admin.notes.delete');

Route::post('/encryption', [PersonalNotesController::class, 'encryption'])
    ->name('admin.notes.encryption');

Route::post('/categories', [PersonalNotesController::class, 'categorySave'])
    ->can('create', 'App\Models\PersonalNotes\PersonalNoteCategories')
    ->name('admin.notes.categories.create');

Route::post('/categories/{category}', [PersonalNotesController::class, 'categorySave'])
    ->can('own', 'category')
    ->name('admin.notes.categories.update');

Route::get('/categories/{category}', [PersonalNotesController::class, 'categories'])
    ->can('own', 'category')
    ->name('admin.notes.category');

Route::post('/categories/delete/{category?}',
    [PersonalNotesController::class, 'categoryDelete'])
    ->can('own', 'category')
    ->name('admin.notes.categories.delete');

Route::get('/categories', [PersonalNotesController::class, 'categories'])
    ->name('admin.notes.categories');
