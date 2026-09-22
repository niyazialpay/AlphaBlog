<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kullanici basina bildirim tercihleri.
 *
 * Tercih KULLANICININ KENDISINE ait, global bir yonetim ayari degil: herkes
 * kendi yetkisinin izin verdigi olaylar arasindan secer (bkz.
 * App\Support\Notifications\NotificationEvents::forUser).
 *
 * Satir YOKSA varsayilan uygulanir (NotificationEvents icindeki `default`),
 * yani yeni bir olay eklendiginde kimseye migration yazmak gerekmez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event', 64);
            // Panel zili / bildirimler ekrani
            $table->boolean('database')->default(true);
            // Tarayici push bildirimi
            $table->boolean('push')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
