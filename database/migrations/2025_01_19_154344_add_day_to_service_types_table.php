<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayToServiceTypesTable extends Migration
{
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->integer('day')->nullable()->after('distance'); // Ajouter la colonne `day`
        });
    }

    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('day'); // Supprimer la colonne `day` en cas de rollback
        });
    }
}
