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
        Schema::create('profile_privacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('show_name')->default(true);
            $table->boolean('show_surname')->default(true);
            $table->boolean('show_location')->default(true);
            $table->boolean('show_education')->default(true);
            $table->boolean('show_job_title')->default(true);
            $table->boolean('show_skills')->default(true);
            $table->boolean('show_about')->default(true);
            $table->boolean('show_social_links')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_privacies');
    }
};
