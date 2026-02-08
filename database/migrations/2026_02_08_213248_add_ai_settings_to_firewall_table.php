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
        Schema::table('firewall', function (Blueprint $table) {
            $table->boolean('ai_review_enabled')->default(false)->after('bad_bots');
            $table->boolean('ai_enforcement_enabled')->default(false)->after('ai_review_enabled');
            $table->string('ai_provider', 64)->nullable()->after('ai_enforcement_enabled');
            $table->string('ai_model', 128)->nullable()->after('ai_provider');
            $table->unsignedTinyInteger('ai_confidence_threshold')->default(85)->after('ai_model');
            $table->unsignedTinyInteger('ai_sample_rate')->default(0)->after('ai_confidence_threshold');
            $table->unsignedInteger('ai_cache_ttl_seconds')->default(900)->after('ai_sample_rate');
            $table->unsignedTinyInteger('ai_timeout_seconds')->default(6)->after('ai_cache_ttl_seconds');
            $table->unsignedInteger('ai_max_payload_chars')->default(3000)->after('ai_timeout_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firewall', function (Blueprint $table) {
            $table->dropColumn([
                'ai_review_enabled',
                'ai_enforcement_enabled',
                'ai_provider',
                'ai_model',
                'ai_confidence_threshold',
                'ai_sample_rate',
                'ai_cache_ttl_seconds',
                'ai_timeout_seconds',
                'ai_max_payload_chars',
            ]);
        });
    }
};
