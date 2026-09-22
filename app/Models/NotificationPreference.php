<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kullanicinin bir olay icin kanal tercihi.
 *
 * Satir YOKSA `NotificationEvents::defaults()` uygulanir; bu sayede yeni bir
 * olay eklendiginde mevcut kullanicilar icin satir uretmek gerekmiyor.
 */
class NotificationPreference extends Model
{
    protected $table = 'notification_preferences';

    protected $fillable = [
        'user_id',
        'event',
        'database',
        'push',
    ];

    protected function casts(): array
    {
        return [
            'database' => 'boolean',
            'push' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
