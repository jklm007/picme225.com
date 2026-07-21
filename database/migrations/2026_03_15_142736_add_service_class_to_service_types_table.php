<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceClassToServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     * Adds service_class to service_types so that each interurban service type
     * can be classified as 'VIP' (climatisé) or 'NORMAL'.
     */
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'service_class')) {
                $table->string('service_class', 20)
                      ->default('NORMAL')
                      ->after('type')
                      ->comment('VIP (climatisé) ou NORMAL pour services interurbains');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('service_class');
        });
    }
}
