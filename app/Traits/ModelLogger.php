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
            if (count($model->getDirty()) === 1 && array_key_exists('views', $model->getDirty())) {
                return;
            }

            self::logAction($model, 'update', oldData: $model->getOriginal(), newData: $model->toArray());
        });

        static::deleted(function ($model) {
            $action = in_array(SoftDeletes::class, class_uses($model)) ? 'soft_delete' : 'delete';

            self::logAction($model, $action, oldData: $model->getOriginal(), newData: null);
        });

        if (in_array(SoftDeletes::class, class_uses(static::class))) {
            static::restored(function ($model) {
                self::logAction($model, 'restore', oldData: null, newData: $model->toArray());
            });

            static::forceDeleted(function ($model) {
                self::logAction($model, 'force_delete', oldData: $model->getOriginal(), newData: null);
            });
        }
    }

    protected static function logAction($model, string $action, ?array $oldData, ?array $newData): void
    {
        $oldData = self::redactLogData($oldData);
        $newData = self::redactLogData($newData);

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

    /**
     * Keys whose values must never be written to the audit log. getOriginal()/
     * toArray() bypass the model's $hidden, so credential material would otherwise
     * be persisted in plaintext-equivalent form (hashes / encrypted 2FA secrets).
     *
     * @return array<int, string>
     */
    protected static function loggerRedactedKeys(): array
    {
        return [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ];
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @return array<string, mixed>|null
     */
    protected static function redactLogData(?array $data): ?array
    {
        if (! $data) {
            return $data;
        }

        foreach (static::loggerRedactedKeys() as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }
}
