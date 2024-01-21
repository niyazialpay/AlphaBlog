<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SocialNetworks extends Model
{
    protected $collection = 'social_networks';

    protected $fillable = [
        'name',
        'url',
        'icon',
        'type',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}
