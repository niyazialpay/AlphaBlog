<?php

namespace App\Models\Settings;

use App\Models\Logs;
use Illuminate\Database\Eloquent\Model;

class SocialSettings extends Model
{
    protected $fillable = [
        'social_networks_header',
        'social_networks_footer',
    ];

    public $timestamps = false;

    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            cache()->forget(config('cache.prefix').'social_networks');
            Logs::create([
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'port' => request()->getPort(),
                'old_data' => json_encode($model->getOriginal()),
                'new_data' => json_encode($model->toArray()),
                'model' => 'SocialSettings',
                'action' => 'update'
            ]);
        });
    }
}
