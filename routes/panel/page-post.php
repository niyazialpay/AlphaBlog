<?php

use App\Http\Controllers\Admin\Post\PostController;
use App\Http\Controllers\Admin\Post\PostQrAdminController;
use App\Http\Middleware\CheckPostType;
use App\Http\Middleware\MergePostTypeToRequest;
use Illuminate\Support\Facades\Route;

Route::any('/', [PostController::class, 'index'])
    ->name('admin.posts');

Route::get('/create', [PostController::class, 'create'])
    ->middleware(CheckPostType::class)
    ->name('admin.post.create')
    ->can('create', 'App\Models\Post\Posts');

Route::post('/save', [PostController::class, 'save'])
    ->middleware([CheckPostType::class, MergePostTypeToRequest::class])
    ->name('admin.post.save')
    ->can('create', 'App\Models\Post\Posts');

Route::get('/{post}/media', [PostController::class, 'media'])
    ->name('admin.post.media');

Route::get('/{post?}/edit', [PostController::class, 'create'])
    ->name('admin.post.edit')
    ->middleware(CheckPostType::class)
    ->can('edit', 'post');

Route::post('/save/{post}', [PostController::class, 'save'])
    ->name('admin.post.update')
    ->middleware([CheckPostType::class, MergePostTypeToRequest::class])
    ->can('edit', 'post');

Route::post('/{post}/delete', [PostController::class, 'delete'])
    ->name('admin.post.delete')
    ->can('delete', 'post');

Route::post('/{post}/delete/permanent', [PostController::class, 'forceDelete'])
    ->name('admin.post.delete.permanent')
    ->can('admin', 'App\Models\User')
    ->withTrashed();

Route::post('/{post}/restore', [PostController::class, 'restore'])
    ->name('admin.post.restore')
    ->can('admin', 'App\Models\User')
    ->withTrashed();

Route::post('/image/delete/{post?}', [PostController::class, 'imageDelete'])
    ->can('delete', 'post')
    ->name('admin.post.image.delete');

Route::any('/category/{category}', [PostController::class, 'index'])
    ->name('admin.post.category');

Route::post('/editor/image/upload',
    [PostController::class, 'editorImageUpload'])
    ->middleware([CheckPostType::class, MergePostTypeToRequest::class])
    ->can('createPost', 'App\Models\Post\Posts');

Route::post('/editor/image/upload/{post?}',
    [PostController::class, 'editorImageUpload'])
    ->middleware([CheckPostType::class, MergePostTypeToRequest::class])
    ->can('edit', 'post')
    ->name('admin.post.editor.image.upload');

Route::post('/editor/image/delete/{post}',
    [PostController::class, 'postImageDelete'])
    ->can('delete', 'post')
    ->name('admin.post.media.delete');

Route::post('/{post}/qr/generate',
    [PostQrAdminController::class, 'generate'])
    ->can('edit', 'post')
    ->name('admin.post.qr.generate');
