<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LinkAgentToCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('station_agents', function (Blueprint $table) {
            if (!Schema::hasColumn('station_agents', 'interurban_company_id')) {
                $table->unsignedBigInteger('interurban_company_id')->nullable()->after('pdp_stop_id');
                $table->foreign('interurban_company_id')->references('id')->on('interurban_companies')->onDelete('set null');
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
        Schema::table('station_agents', function (Blueprint $table) {
            $table->dropForeign(['interurban_company_id']);
            $table->dropColumn('interurban_company_id');
        });
    }
}
