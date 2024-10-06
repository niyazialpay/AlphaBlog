<?php

namespace App\Models;

use Cloudflare\API\Endpoints\Zones;
use Illuminate\Database\Eloquent\Model;

class Cloudflare extends Model
{
    protected $table = 'cloudflare';

    protected $fillable = [
        'cf_email',
        'cf_key',
        'domain'
    ];

    public static string $zoneID;
    public static Zones $zones;
    public static string $domain;

    protected function casts(): array
    {
        return [
            'cf_email' => 'encrypted',
            'cf_key' => 'encrypted',
            'domain' => 'string'
        ];
    }


}
