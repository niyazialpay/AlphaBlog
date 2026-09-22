<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            $table->text('maps')->change();
        });
    }

    public function down(): void
    {
        Schema::table('contact_pages', function (Blueprint $table) {
            $table->string('maps')->change();
        });
    }
};
