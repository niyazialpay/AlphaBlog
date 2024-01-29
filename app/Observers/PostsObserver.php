<?php

namespace App\Observers;

use App\Models\PostHistory;
use App\Models\Posts;

class PostsObserver
{
    public function updated(Posts $post): void
    {
        $original = $post->getOriginal();
        PostHistory::create([
            'post_id' => $post->_id,
            'title' => $original['title'],
            'content' => $original['content'],
            'slug' => $original['slug'],
            'user_id' => $original['user_id'],
        ]);
    }


    public function deleted(Posts $post): void
    {
        PostHistory::where('post_id', $post->_id)->delete();
    }


    public function restored(Posts $post): void
    {
        PostHistory::withTrashed()->where('post_id', $post->_id)->restore();
    }


    public function forceDeleted(Posts $post): void
    {
        PostHistory::withTrashed()->where('post_id', $post->_id)->forceDelete();
    }
}
