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
        Schema::table('social_networks', function (Blueprint $table) {
            $table->string('twitch')->nullable()->after('website');
            $table->string('telegram')->nullable()->after('twitch');
            $table->string('discord')->nullable()->after('telegram');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_networks', function (Blueprint $table) {
            $table->dropColumn('twitch');
            $table->dropColumn('telegram');
            $table->dropColumn('discord');
        });
    }
};
