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
            $table->string('linkedin');
            $table->string('github');
            $table->string('instagram');
            $table->string('x');
            $table->string('bluesky');
            $table->string('facebook');
            $table->string('devto');
            $table->string('medium');
            $table->string('youtube');
            $table->string('reddit');
            $table->string('xbox');
            $table->string('deviantart');
            $table->string('website');
            $table->enum('type', ['user', 'website']);
            $table->id('user_id')->index();
            $table->foreign('user_id')->references('_id')->on('users')->onDelete('cascade');
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
