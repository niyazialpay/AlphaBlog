<?php

namespace App\Models\Post;

use App\Models\User;
use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    use ModelLogger;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }
}
