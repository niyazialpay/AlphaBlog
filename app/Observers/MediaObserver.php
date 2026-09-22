<?php

namespace App\Observers;

use App\Models\Logs;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaObserver
{
    public function created(Media $media): void
    {
        Logs::create([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'port' => request()->getPort(),
            'old_data' => null,
            'new_data' => json_encode($media->toArray()),
            'model' => 'Media',
            'action' => 'create',
        ]);
    }

    public function updated(Media $media): void
    {
        Logs::create([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'port' => request()->getPort(),
            'old_data' => json_encode($media->getOriginal()),
            'new_data' => json_encode($media->toArray()),
            'model' => 'Media',
            'action' => 'update',
        ]);
    }

    public function deleted(Media $media): void
    {
        Logs::create([
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'port' => request()->getPort(),
            'old_data' => json_encode($media->getOriginal()),
            'new_data' => null,
            'model' => 'Media',
            'action' => 'delete',
        ]);
    }
}
