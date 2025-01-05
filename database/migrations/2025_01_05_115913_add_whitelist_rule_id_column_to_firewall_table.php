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
            $table->unsignedBigInteger('whitelist_rule_id')->nullable()->index()->after('is_active');
            $table->foreign('whitelist_rule_id')->references('id')->on('ip_filters')->nullOnDelete();
            $table->renameColumn('ip_filter_id', 'blacklist_rule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('firewall', function (Blueprint $table) {
            //
        });
    }
};
