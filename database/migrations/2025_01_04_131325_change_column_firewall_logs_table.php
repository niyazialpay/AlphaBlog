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
        Schema::table('firewall_logs', function (Blueprint $table) {
            $table->dropForeign(['ip_list_id']);
            $table->dropColumn('ip_list_id');
            $table->unsignedBigInteger('ip_list_id')->nullable()->index();
            $table->foreign('ip_list_id')->references('id')->on('ip_lists')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
