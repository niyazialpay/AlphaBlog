<?php

namespace App\Models\Settings;

use App\Models\Logs;
use Illuminate\Database\Eloquent\Model;

class AdvertiseSettings extends Model
{
    protected $fillable = [
        'google_ad_manager',
        'square_display_advertise',
        'vertical_display_advertise',
        'horizontal_display_advertise',
    ];

    public $timestamps = false;

    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            cache()->forget(config('cache.prefix').'advertise_settings');
            Logs::create([
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'port' => request()->getPort(),
                'old_data' => json_encode($model->getOriginal()),
                'new_data' => json_encode($model->toArray()),
                'model' => 'AdvertiseSettings',
                'action' => 'update'
            ]);
        });
    }
}
