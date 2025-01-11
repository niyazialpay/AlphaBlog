<?php

namespace App\Models\Post;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostCategory extends Model
{
    use ModelLogger;

    protected $table = 'post_categories';

    protected $fillable = [
        'post_id',
        'category_id',
        'is_primary',
    ];

    protected $attributes = [
        'is_primary' => false,
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'category_id');
    }
}
