<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcFieldsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'ac_price')) {
                $table->integer('ac_price')->default(0)->after('price');
            }
        });

        Schema::table('provider_services', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_services', 'has_ac')) {
                $table->boolean('has_ac')->default(false)->after('status');
            }
        });

        Schema::table('user_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('user_requests', 'ac')) {
                $table->boolean('ac')->default(false)->after('ride_variant');
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
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('ac_price');
        });

        Schema::table('provider_services', function (Blueprint $table) {
            $table->dropColumn('has_ac');
        });

        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn('ac');
        });
    }
}
