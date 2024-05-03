<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialNetworks extends Model
{
    protected $table = 'social_networks';

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
        'website',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
