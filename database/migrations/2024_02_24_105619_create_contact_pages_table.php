<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('maps')->nullable();
            $table->string('language', 3)->index();
            $table->foreign('language')->references('code')->on('languages')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_pages');
    }
};
