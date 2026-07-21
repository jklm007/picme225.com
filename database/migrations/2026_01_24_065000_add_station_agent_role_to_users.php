<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStationAgentRoleToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add STATION_AGENT to user_type enum
        Schema::table('users', function (Blueprint $table) {
            // In Laravel/MySQL, we can't easily update enum values without raw SQL or recreating
            // Since we are in development, it's safer to use raw SQL for speed or just modify the column
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('USER', 'FLEET', 'STATION_AGENT') DEFAULT 'USER'");
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
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('USER', 'FLEET') DEFAULT 'USER'");
            }
        });
    }
}
