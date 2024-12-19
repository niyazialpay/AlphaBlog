<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminOneSignal extends Model
{
    public $timestamps = false;

    protected $table = 'admin_one_signal';

    protected $fillable = [
        'onesignal',
    ];

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
                'model' => 'AdminOneSignal',
                'action' => 'update'
            ]);
        });
    }
}
