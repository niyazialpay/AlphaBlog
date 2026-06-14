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
        Schema::table('analytics_settings', function (Blueprint $table) {
            $table->string('ga_measurement_id')->nullable()->after('google_analytics');
            $table->string('ga_api_secret')->nullable()->after('ga_measurement_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_settings', function (Blueprint $table) {
            $table->dropColumn(['ga_measurement_id', 'ga_api_secret']);
        });
    }
};
