<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pdp_routes MODIFY COLUMN type ENUM('COMMUNAL', 'INTER_COMMUNAL', 'INTERURBAN') DEFAULT 'COMMUNAL'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pdp_routes MODIFY COLUMN type ENUM('COMMUNAL', 'INTER_COMMUNAL') DEFAULT 'COMMUNAL'");
        }
    }
};
