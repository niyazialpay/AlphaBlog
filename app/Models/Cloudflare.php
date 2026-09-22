<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cloudflare extends Model
{
    protected $table = 'cloudflare';

    protected $fillable = [
        'cf_email',
        'cf_key',
        'domain',
    ];

    public static function zoneCacheKey(string $domain): string
    {
        return config('cache.prefix').'cf_zone_id_'.$domain;
    }

    protected function casts(): array
    {
        return [
            'cf_email' => 'encrypted',
            'cf_key' => 'encrypted',
            'domain' => 'string',
        ];
    }
}
