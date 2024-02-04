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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url');
            $table->string('target')->default('_self');
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->string('parent_id')->nullable();
            $table->string('language')->index();
            $table->enum('menu_type', ['standard', 'category'])->index();
            $table->string('menu_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
