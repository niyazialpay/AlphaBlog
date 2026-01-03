<?php

namespace App\Models\Settings;

use App\Models\Logs;
use App\Traits\ModelLogger;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSettings extends Model
{
    use ModelLogger;

    protected $fillable = [
        'google_analytics',
        'yandex_metrica',
        'fb_pixel',
        'log_rocket',
    ];

    public $timestamps = false;

}
