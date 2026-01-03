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
        Schema::create('advertise_settings', function (Blueprint $table) {
            $table->id();
            $table->text('google_ad_manager')->nullable();
            $table->text('square_display_advertise')->nullable();
            $table->text('vertical_display_advertise')->nullable();
            $table->text('horizontal_display_advertise')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertise_settings');
    }
};
