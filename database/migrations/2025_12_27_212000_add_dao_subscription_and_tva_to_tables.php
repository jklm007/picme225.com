<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDaoSubscriptionAndTvaToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            if (!Schema::hasColumn('providers', 'subscription_level')) {
                $table->enum('subscription_level', ['none', 'standard', 'eco', 'pro'])->default('none')->after('status');
            }
        });

        Schema::table('user_request_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('user_request_payments', 'tva_fee')) {
                $table->decimal('tva_fee', 10, 2)->default(0)->after('tax');
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
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('subscription_level');
        });

        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn('tva_fee');
        });
    }
}
