<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowedServiceTypesToPdpRouteSegments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pdp_route_segments', function (Blueprint $table) {
            $table->json('allowed_service_types')->nullable()->after('service_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pdp_route_segments', function (Blueprint $table) {
            $table->dropColumn('allowed_service_types');
        });
    }
}
