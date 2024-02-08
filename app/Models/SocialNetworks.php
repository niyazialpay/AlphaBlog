<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SocialNetworks extends Model
{
    protected $collection = 'social_networks';

    protected $fillable = [
        'type',
        'user_id',
        'linkedin',
        'facebook',
        'x',
        'bluesky',
        'instagram',
        'github',
        'devto',
        'medium',
        'youtube',
        'reddit',
        'xbox',
        'deviantart',
        'website'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}
