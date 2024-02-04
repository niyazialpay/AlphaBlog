<?php
Route::get('/', [App\Http\Controllers\Admin\Post\CommentController::class, 'index'])
    ->can('view', 'App\Models\Post\Comments')
    ->name('admin.post.comments');

Route::post('/{comment}/delete', [App\Http\Controllers\Admin\Post\CommentController::class, 'delete'])
    ->can('delete', 'comment')
    ->name('admin.post.comments.delete');

Route::post('/{comment}/restore', [App\Http\Controllers\Admin\Post\CommentController::class, 'restore'])
    ->can('edit', 'comment')
    ->name('admin.post.comments.restore')->withTrashed();

Route::post('/{comment}/delete/permanent', [App\Http\Controllers\Admin\Post\CommentController::class, 'forceDelete'])
    ->can('delete', 'comment')
    ->name('admin.post.comments.force-delete')->withTrashed();

Route::post('/{comment}/edit', [App\Http\Controllers\Admin\Post\CommentController::class, 'edit'])
    ->can('edit', 'comment')
    ->name('admin.post.comments.edit');

Route::post('/{comment}/approve', [App\Http\Controllers\Admin\Post\CommentController::class, 'approve'])
    ->can('edit', 'comment')
    ->name('admin.post.comments.approve');

Route::post('/{comment}/disapprove', [App\Http\Controllers\Admin\Post\CommentController::class, 'disapprove'])
    ->can('edit', 'comment')
    ->name('admin.post.comments.disapprove');

Route::post('/save', [App\Http\Controllers\Admin\Post\CommentController::class, 'save'])
    ->can('create', 'App\Models\Post\Comments')
    ->name('admin.post.comments.create');

Route::post('/save/{comment}', [App\Http\Controllers\Admin\Post\CommentController::class, 'save'])
    ->can('edit', 'comment')
    ->name('admin.post.comments.update');
