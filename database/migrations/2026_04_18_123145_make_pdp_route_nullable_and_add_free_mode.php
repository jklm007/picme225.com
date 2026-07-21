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
        Schema::table('active_shared_rides', function (Blueprint $table) {
            $table->unsignedBigInteger('pdp_route_id')->nullable()->change();
            $table->boolean('is_free_mode')->default(false)->after('pdp_route_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('active_shared_rides', function (Blueprint $table) {
            $table->unsignedBigInteger('pdp_route_id')->nullable(false)->change();
            $table->dropColumn('is_free_mode');
        });
    }
};
