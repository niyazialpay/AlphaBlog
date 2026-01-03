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
        Schema::create('firewall', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->foreignId('ip_filter_id')->constrained('ip_filters')->restrictOnDelete();

            $table->boolean('check_referer')->nullable();
            $table->boolean('check_bots')->nullable();
            $table->boolean('check_request_method')->nullable();
            $table->boolean('check_dos')->nullable();
            $table->boolean('check_union_sql')->nullable();
            $table->boolean('check_click_attack')->nullable();
            $table->boolean('check_xss')->nullable();
            $table->boolean('check_cookie_injection')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firewalls');
    }
};
