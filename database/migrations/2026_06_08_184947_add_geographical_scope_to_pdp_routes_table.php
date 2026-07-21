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
        Schema::table('pdp_routes', function (Blueprint $table) {
            $table->boolean('is_intercommunal')->default(false)->after('is_active');
            $table->boolean('is_communal')->default(false)->after('is_intercommunal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_routes', function (Blueprint $table) {
            $table->dropColumn(['is_intercommunal', 'is_communal']);
        });
    }
};
