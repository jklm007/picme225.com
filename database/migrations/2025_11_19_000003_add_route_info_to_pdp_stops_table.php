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
            if (!Schema::hasColumn('pdp_stops', 'pdp_route_id')) {
                $table->unsignedBigInteger('pdp_route_id')->nullable()->after('id');
                $table->integer('order')->nullable()->after('pdp_route_id')->comment('Ordre de l\'arrêt dans l\'itinéraire');
                
                $table->foreign('pdp_route_id')->references('id')->on('pdp_routes')->onDelete('cascade');
                $table->index(['pdp_route_id', 'order']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            if (Schema::hasColumn('pdp_stops', 'pdp_route_id')) {
                $table->dropForeign(['pdp_route_id']);
                $table->dropIndex(['pdp_route_id', 'order']);
                $table->dropColumn(['pdp_route_id', 'order']);
            }
        });
    }
};

