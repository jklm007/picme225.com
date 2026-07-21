<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserTypeAndFleetLinkToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['USER', 'FLEET'])->default('USER')->after('id');
            }
            if (!Schema::hasColumn('users', 'fleet_id')) {
                $table->unsignedInteger('fleet_id')->nullable()->after('user_type');
                $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('set null');
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fleet_id']);
            $table->dropColumn(['user_type', 'fleet_id']);
        });
    }
}
