<?php

namespace App\Models;


use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use MongoDB\Laravel\Relations\BelongsTo;

class Comments extends Model
{
    use SoftDeletes;

    protected $collection = 'comments';
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
        'is_approved',
        'name',
        'email',
        'ip_address',
        'user_agent',
    ];

    protected $attributes = [
        'is_approved' => false,
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id', '_id');
    }
}
