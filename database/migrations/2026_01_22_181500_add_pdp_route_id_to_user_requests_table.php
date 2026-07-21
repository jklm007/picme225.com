<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdpRouteIdToUserRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('pdp_route_id')->nullable()->after('interurban_company_id');
            $table->foreign('pdp_route_id')->references('id')->on('pdp_routes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropForeign(['pdp_route_id']);
            $table->dropColumn('pdp_route_id');
        });
    }
}
