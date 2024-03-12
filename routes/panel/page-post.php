<?php
Route::get('/', [App\Http\Controllers\Admin\Post\PostController::class, 'index'])
    ->name('admin.posts');

Route::get('/create', [App\Http\Controllers\Admin\Post\PostController::class, 'create'])
    ->middleware('check_post_type')
    ->name('admin.post.create')
    ->can('create', 'App\Models\Post\Posts');

Route::post('/save', [App\Http\Controllers\Admin\Post\PostController::class, 'save'])
    ->middleware(['merge_post_type', 'check_post_type'])
    ->name('admin.post.save')
    ->can('create', 'App\Models\Post\Posts');

Route::get('/{post}/media', [App\Http\Controllers\Admin\Post\PostController::class, 'media'])
    ->name('admin.post.media');

Route::get('/{post}/edit', [App\Http\Controllers\Admin\Post\PostController::class, 'create'])
    ->name('admin.post.edit')
    ->middleware('check_post_type')
    ->can('edit', 'post');

Route::post('/save/{post}', [App\Http\Controllers\Admin\Post\PostController::class, 'save'])
    ->name('admin.post.update')
    ->middleware(['merge_post_type', 'check_post_type'])
    ->can('edit', 'post');

Route::post('/{post}/delete', [App\Http\Controllers\Admin\Post\PostController::class, 'delete'])
    ->name('admin.post.delete')
    ->can('delete', 'post');

Route::post('/{post}/delete/permanent', [App\Http\Controllers\Admin\Post\PostController::class, 'forceDelete'])
    ->name('admin.post.delete.permanent')
    ->can('delete', 'post')
    ->withTrashed();

Route::post('/{post}/restore', [App\Http\Controllers\Admin\Post\PostController::class, 'restore'])
    ->name('admin.post.restore')
    ->can('edit', 'post')
    ->withTrashed();


Route::post('/image/delete/{post?}', [App\Http\Controllers\Admin\Post\PostController::class, 'imageDelete'])
    ->can('delete', 'post')
    ->name('admin.post.image.delete');

Route::get('/{category}', [App\Http\Controllers\Admin\Post\PostController::class, 'index'])
    ->name('admin.post.category');

Route::post('/editor/image/upload',
    [App\Http\Controllers\Admin\Post\PostController::class, 'editorImageUpload'])
    ->can('createPost', 'App\Models\Post\Posts')
    ->name('admin.post.editor.image.upload');

Route::post('/editor/image/upload/{post?}',
    [App\Http\Controllers\Admin\Post\PostController::class, 'editorImageUpload'])
    ->can('edit', 'post')
    ->name('admin.post.editor.image.upload');

Route::post('/editor/image/delete/{post}',
    [App\Http\Controllers\Admin\Post\PostController::class, 'postImageDelete'])
    ->can('delete', 'post')
    ->name('admin.post.media.delete');
