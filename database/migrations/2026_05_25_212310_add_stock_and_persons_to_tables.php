<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(1)->after('price');
        });

        Schema::table('event_pass_types', function (Blueprint $table) {
            $table->integer('persons_per_pass')->default(1)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn('stock_quantity');
        });

        Schema::table('event_pass_types', function (Blueprint $table) {
            $table->dropColumn('persons_per_pass');
        });
    }
};
