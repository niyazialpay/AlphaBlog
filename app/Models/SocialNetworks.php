<?php

namespace App\Models;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialNetworks extends Model
{
    use ModelLogger;

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
        'twitch',
        'telegram',
        'discord',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
