<?php
Route::post('/editor/image/upload/{note?}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'editorImageUpload'])
    ->can('own', 'App\Models\PersonalNotes')
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

Route::post('/save/{note?}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'save'])
    ->can('own', 'App\Models\PersonalNotes')
    ->name('admin.notes.save');


Route::get('/{note}/media', [App\Http\Controllers\Admin\PersonalNotesController::class, 'media'])
    ->can('own', 'note')
    ->name('admin.notes.media');

Route::post('/delete/{note}', [App\Http\Controllers\Admin\PersonalNotesController::class, 'delete'])
    ->can('own', 'App\Models\PersonalNotes')
    ->name('admin.notes.delete');
