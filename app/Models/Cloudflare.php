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

    /**
     * Zone ID çözümü canlı bir Cloudflare API çağrısı; her istekte tekrarlanmamalı.
     * Anahtar burada tanımlı ki controller'lar ve ayar kaydetme aynı anahtarı kullansın.
     */
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
