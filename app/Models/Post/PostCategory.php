<?php

namespace App\Models\Post;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class PostCategory extends Model
{

    protected $collection = 'post_categories';

    protected $fillable = [
        'post_id',
        'category_id',
        'is_primary'
    ];

    protected $attributes = [
        'is_primary' => false
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id', '_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id', '_id');
    }

}
