<?php

namespace App\Models\Post;

use App\Models\User;
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

    protected $appends = [
        'nickname',
        'user_email',
    ];

    public function getNicknameAttribute(): string
    {
        return $this->user_id ? $this->user->nickname : $this->name;
    }

    public function getUserEmailAttribute(): string
    {
        return $this->user_id ? $this->user->email : $this->email;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id', '_id');
    }
}
