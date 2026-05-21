<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('widget_type', 60);
            $table->tinyInteger('gs_x')->default(0);
            $table->tinyInteger('gs_y')->default(0);
            $table->tinyInteger('gs_w')->default(3);
            $table->tinyInteger('gs_h')->default(2);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
