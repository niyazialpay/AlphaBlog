<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Web Push abonelikleri.
 *
 * Bir kullanicinin BIRDEN COK abonelige sahip olmasi normaldir: her tarayici ve
 * her cihaz ayri bir endpoint uretir. Bu yuzden benzersizlik kullanici basina
 * degil ENDPOINT basinadir.
 *
 * `endpoint` cok uzun olabildigi icin (FCM adresleri 300+ karakter) TEXT olarak
 * tutulur; MySQL'de TEXT dogrudan unique index alamadigindan yaninda
 * `endpoint_hash` kolonu var ve benzersizlik onun uzerinden saglaniyor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding', 32)->default('aesgcm');
            $table->text('user_agent')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
