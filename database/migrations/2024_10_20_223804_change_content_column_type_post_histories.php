<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_histories', function (Blueprint $table) {
            $table->longText('content')->change();
        });
    }

    public function down(): void {}
};
