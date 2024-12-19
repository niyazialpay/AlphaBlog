<?php

namespace App\Models\Settings;

use App\Models\Logs;
use Illuminate\Database\Eloquent\Model;

class SeoSettings extends Model
{
    protected $fillable = [
        'site_name',
        'title',
        'description',
        'keywords',
        'author',
        'robots',
        'language',
    ];

    public $timestamps = false;

    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            Logs::create([
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'port' => request()->getPort(),
                'old_data' => json_encode($model->getOriginal()),
                'new_data' => json_encode($model->toArray()),
                'model' => 'SeoSettings',
                'action' => 'update'
            ]);
        });
    }
}
