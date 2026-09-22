<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onesignal', function (Blueprint $table) {
            $table->id();
            $table->text('app_id');
            $table->text('auth_key');
            $table->string('safari_web_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onesignal');
    }
};
