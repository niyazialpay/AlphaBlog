<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_one_signal', function (Blueprint $table) {
            $table->id();
            $table->text('onesignal')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_one_signal');
    }
};
