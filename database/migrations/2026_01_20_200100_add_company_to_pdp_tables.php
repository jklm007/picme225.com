<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyToPdpTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->unsignedBigInteger('interurban_company_id')->nullable()->after('id');
            $table->foreign('interurban_company_id')->references('id')->on('interurban_companies')->onDelete('set null');
        });

        Schema::table('pdp_routes', function (Blueprint $table) {
            $table->unsignedBigInteger('interurban_company_id')->nullable()->after('id');
            $table->foreign('interurban_company_id')->references('id')->on('interurban_companies')->onDelete('set null');
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
            $table->dropForeign(['interurban_company_id']);
            $table->dropColumn('interurban_company_id');
        });

        Schema::table('pdp_routes', function (Blueprint $table) {
            $table->dropForeign(['interurban_company_id']);
            $table->dropColumn('interurban_company_id');
        });
    }
}
