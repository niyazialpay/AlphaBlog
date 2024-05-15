<?php

namespace App\Models\Post;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    use SoftDeletes;

    protected $table = 'comments';

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

    /*protected $appends = [
        'nickname',
        'user_email',
    ];*/

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }

    /*public function getNicknameAttribute(): string
    {
        return $this->user_id ? $this->user()->first()->nickname : $this->name;
    }

    public function getUserEmailAttribute(): string
    {
        return $this->user_id ? $this->user()->first()->email : $this->email;
    }*/
}
