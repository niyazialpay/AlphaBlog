<?php

namespace App\Models\Post;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostQrScan extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'post_qr_scans';

    protected $fillable = ['post_id', 'session_id', 'ip_address'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Posts::class, 'post_id');
    }
}
