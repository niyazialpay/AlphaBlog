<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webauthn_credentials', function (Blueprint $table) {
            $table->unsignedBigInteger('authenticatable_id')->index()->change();
            $table->foreign('authenticatable_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('webauthn_credentials', function (Blueprint $table) {
            $table->dropForeign(['authenticatable_id']);
            $table->dropIndex(['authenticatable_id']);
        });
    }
};
