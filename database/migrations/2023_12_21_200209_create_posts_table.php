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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->integer('views')->default(0);
            $table->string('user_id')->index();
            $table->enum('post_type', ['post', 'page'])->default('post');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('language')->index();
            $table->boolean('is_published')->default(false);
            $table->foreign('user_id')->references('_id')->on('users')->onDelete('cascade');
            $table->foreign('language')->references('code')->on('languages')->onDelete('cascade');
            $table->string('href_lang')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
