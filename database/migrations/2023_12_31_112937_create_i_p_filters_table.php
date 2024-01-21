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
        Schema::create('ip_filters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('ip_range');
            $table->json('routes')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->enum('list_type', ['blacklist', 'whitelist'])->default('blacklist')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_filters');
    }
};
