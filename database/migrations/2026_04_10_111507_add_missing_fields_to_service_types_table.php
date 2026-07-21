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
        Schema::table('service_types', function (Blueprint $table) {
            $table->integer('max_detour_communal')->default(0)->after('max_detour');
            $table->integer('max_detour_intercommunal')->default(0)->after('max_detour_communal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['max_detour_communal', 'max_detour_intercommunal']);
        });
    }
};
