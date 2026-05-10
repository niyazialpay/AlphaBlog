<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('homepage_featured_count')->default(5)->after('sharethis');
            $table->unsignedSmallInteger('homepage_recent_count')->default(45)->after('homepage_featured_count');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['homepage_featured_count', 'homepage_recent_count']);
        });
    }
};
