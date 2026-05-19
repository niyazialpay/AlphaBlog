<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('google_indexing_enabled')->default(false)->after('llms_txt_instructions');
            $table->text('google_indexing_credentials')->nullable()->after('google_indexing_enabled');
            $table->unsignedSmallInteger('google_indexing_daily_limit')->default(200)->after('google_indexing_credentials');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['google_indexing_enabled', 'google_indexing_credentials', 'google_indexing_daily_limit']);
        });
    }
};
