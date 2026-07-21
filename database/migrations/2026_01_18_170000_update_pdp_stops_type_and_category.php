<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePdpStopsTypeAndCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            if (!Schema::hasColumn('pdp_stops', 'type')) {
                $table->string('type')->default('arret')->after('commune'); // gare, arret
            }
            if (!Schema::hasColumn('pdp_stops', 'vehicle_category')) {
                $table->string('vehicle_category')->default('both')->after('type'); // car, minibus, both
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->dropColumn(['type', 'vehicle_category']);
        });
    }
}
