<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    protected $table = 'contact_pages';

    protected $fillable = [
        'description',
        'meta_description',
        'meta_keywords',
        'maps',
        'language',
        'title',
        'slug',
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
                'model' => 'ContactPage',
                'action' => 'update'
            ]);
        });
    }
}
