<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics_settings', function (Blueprint $table) {
            $table->id();
            $table->string('google_analytics')->nullable();
            $table->string('yandex_metrica')->nullable();
            $table->string('fb_pixel')->nullable();
            $table->string('log_rocket')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_settings');
    }
};
