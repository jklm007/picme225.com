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
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->boolean('is_main_stop')->default(false)->after('is_active');
            $table->boolean('is_verified')->default(false)->after('is_main_stop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->dropColumn(['is_main_stop', 'is_verified']);
        });
    }
};
