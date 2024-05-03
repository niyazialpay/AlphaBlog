<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class AnalyticsSettings extends Model
{
    protected $fillable = [
        'google_analytics',
        'yandex_metrica',
        'fb_pixel',
        'log_rocket',
    ];

    public $timestamps = false;
}
