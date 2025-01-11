<?php

namespace App\Traits;

use App\Models\Logs;
use Illuminate\Database\Eloquent\SoftDeletes;

trait ModelLogger
{
    public static function bootModelLogger(): void
    {
        static::created(function ($model) {
            self::logAction($model, 'create', oldData: null, newData: $model->toArray());
        });

        static::updating(function ($model) {
            // Sadece "view" alanı değiştiyse log yazma
            if (count($model->getDirty()) === 1 && array_key_exists('view', $model->getDirty())) {
                return;
            }

            self::logAction($model, 'update', oldData: $model->getOriginal(), newData: $model->toArray());
        });

        static::deleted(function ($model) {
            // Eğer model SoftDeletes trait'ini kullanıyorsa "soft delete" logla, aksi halde "delete"
            $action = in_array(SoftDeletes::class, class_uses($model)) ? 'soft delete' : 'delete';

            self::logAction($model, $action, oldData: $model->getOriginal(), newData: null);
        });

        // Soft delete destekleyen modeller için restore ve force delete olaylarını dinle
        if (in_array(SoftDeletes::class, class_uses(static::class))) {
            static::restored(function ($model) {
                self::logAction($model, 'restore', oldData: null, newData: $model->toArray());
            });

            static::forceDeleted(function ($model) {
                self::logAction($model, 'force delete', oldData: $model->getOriginal(), newData: null);
            });
        }
    }

    protected static function logAction($model, string $action, ?array $oldData, ?array $newData): void
    {
        $data = [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'port' => request()->getPort(),
            'model' => get_class($model),
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'action' => $action,
        ];

        Logs::create($data);
    }
}
