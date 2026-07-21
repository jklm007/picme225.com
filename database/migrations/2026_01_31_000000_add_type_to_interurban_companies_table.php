<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToInterurbanCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interurban_companies', function (Blueprint $table) {
            $table->enum('type', ['BIG', 'SMALL'])->default('BIG')->after('name');
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
            $table->dropColumn('type');
        });
    }
}
