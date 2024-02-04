<?php

namespace App\Models\Post;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\BelongsTo;

class PostHistory extends Model
{
    use SoftDeletes;

    protected $collection = 'post_histories';

    protected $fillable = [
        'post_id',
        'title',
        'content',
        'slug'
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id', '_id');
    }
}
