<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

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
}
