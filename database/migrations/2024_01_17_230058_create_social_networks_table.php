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
        Schema::create('social_networks', function (Blueprint $table) {
            $table->id();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('instagram')->nullable();
            $table->string('x')->nullable();
            $table->string('bluesky')->nullable();
            $table->string('facebook')->nullable();
            $table->string('devto')->nullable();
            $table->string('medium')->nullable();
            $table->string('youtube')->nullable();
            $table->string('reddit')->nullable();
            $table->string('xbox')->nullable();
            $table->string('deviantart')->nullable();
            $table->string('website')->nullable();
            $table->enum('type', ['user', 'website']);
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_networks');
    }
};
