<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostIndexingLog extends Model
{
    protected $table = 'post_indexing_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'url',
        'type',
        'status',
        'response_code',
        'message',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }
}
