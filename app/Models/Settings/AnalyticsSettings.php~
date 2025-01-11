<?php

namespace App\Models\Settings;

use App\Models\Logs;
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

    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            cache()->forget(config('cache.prefix').'analytic_settings');
            Logs::create([
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'port' => request()->getPort(),
                'old_data' => json_encode($model->getOriginal()),
                'new_data' => json_encode($model->toArray()),
                'model' => 'AnalyticsSettings',
                'action' => 'update'
            ]);
        });
    }
}
