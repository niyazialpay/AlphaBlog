<?php

namespace App\Models\Settings;

use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSettings extends Model
{
    use ModelLogger;

    protected $fillable = [
        'google_analytics',
        'ga_measurement_id',
        'ga_api_secret',
        'yandex_metrica',
        'fb_pixel',
        'log_rocket',
    ];

    public $timestamps = false;
}
