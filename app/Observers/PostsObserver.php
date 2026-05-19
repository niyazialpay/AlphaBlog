<?php

namespace App\Observers;

use App\Jobs\SubmitUrlToGoogleIndex;
use App\Models\Post\PostHistory;
use App\Models\Post\Posts;
use App\Models\Settings\GeneralSettings;

class PostsObserver
{
    public function saved(Posts $post): void
    {
        if (
            $post->is_published
            && ($post->wasRecentlyCreated || $post->wasChanged('is_published'))
            && GeneralSettings::first()?->google_indexing_enabled
            && ! $post->isIndexed()
        ) {
            SubmitUrlToGoogleIndex::dispatch($post);
        }
    }

    public function updated(Posts $post): void
    {
        $original = $post->getOriginal();
        if ($original['views'] == $post->views) {
            PostHistory::create([
                'post_id' => $post->id,
                'title' => $original['title'],
                'content' => $original['content'],
                'slug' => $original['slug'],
                'user_id' => $original['user_id'],
            ]);
        }
    }

    public function deleted(Posts $post): void
    {
        PostHistory::where('post_id', $post->id)->delete();
    }

    public function restored(Posts $post): void
    {
        PostHistory::withTrashed()->where('post_id', $post->id)->restore();
    }

    public function forceDeleted(Posts $post): void
    {
        PostHistory::withTrashed()->where('post_id', $post->id)->forceDelete();
    }
}
