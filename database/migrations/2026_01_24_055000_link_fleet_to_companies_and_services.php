<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LinkFleetToCompaniesAndServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Link InterurbanCompany to Fleet
        Schema::table('interurban_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('interurban_companies', 'fleet_id')) {
                $table->unsignedInteger('fleet_id')->nullable()->after('id');
                $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('set null');
            }
        });

        // 2. Link ServiceType to InterurbanCompany
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'interurban_company_id')) {
                $table->unsignedBigInteger('interurban_company_id')->nullable()->after('id');
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
        Schema::table('interurban_companies', function (Blueprint $table) {
            $table->dropForeign(['fleet_id']);
            $table->dropColumn('fleet_id');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->dropForeign(['interurban_company_id']);
            $table->dropColumn('interurban_company_id');
        });
    }
}
