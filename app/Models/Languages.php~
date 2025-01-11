<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Languages extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'name',
        'code',
        'flag',
        'is_active',
        'is_default',
    ];

    public function getLanguage($code)
    {
        if (Cache::has(config('cache.prefix').'languages_'.$code)) {
            $languages = Cache::get(config('cache.prefix').'languages_'.$code);
        } else {
            $languages = Cache::rememberForever(config('cache.prefix').'languages_'.$code, function () use ($code) {
                return $this::where('is_active', true)
                    ->where('code', $code)
                    ->first();
            });
        }

        return $languages;
    }

    public static function boot(): void
    {
        parent::boot();

        $data = [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'port' => request()->getPort(),
            'model' => 'Languages',
        ];

        static::created(function ($model) use ($data) {
            $data['old_data'] = json_encode($model->getOriginal());
            $data['new_data'] = json_encode($model->toArray());
            $data['action'] = 'create';
            Logs::create($data);
        });

        static::updating(function ($model) use ($data) {
            $data['old_data'] = json_encode($model->getOriginal());
            $data['new_data'] = json_encode($model->toArray());
            $data['action'] = 'update';
            Logs::create($data);
        });

        static::deleted(function ($model) use ($data) {
            $data['old_data'] = json_encode($model->getOriginal());
            $data['new_data'] = json_encode($model->toArray());
            $data['action'] = 'delete';
            Logs::create($data);
        });
    }
}
