<?php

namespace App\Models\Post;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostHistory extends Model
{
    use SoftDeletes;

    protected $table = 'post_histories';

    protected $fillable = [
        'post_id',
        'title',
        'content',
        'slug',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }
}
