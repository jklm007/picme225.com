<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add INTERURBAN to the ENUM
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pdp_routes MODIFY COLUMN type ENUM('COMMUNAL', 'INTER_COMMUNAL', 'INTERURBAN', 'REGIONAL') DEFAULT 'COMMUNAL'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pdp_routes MODIFY COLUMN type ENUM('COMMUNAL', 'INTER_COMMUNAL', 'INTERURBAN') DEFAULT 'COMMUNAL'");
        }
    }
};
