<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_lists', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->foreignId('filter_id')->constrained('ip_filters')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_lists');
    }
};
