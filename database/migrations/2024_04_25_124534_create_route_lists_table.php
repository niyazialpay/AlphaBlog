<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_lists', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->foreignId('filter_id')->constrained('ip_filters')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_lists');
    }
};
