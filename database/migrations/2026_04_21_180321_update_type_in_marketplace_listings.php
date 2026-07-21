<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Antigravity: Modifying MySQL ENUM strictly requires a raw statement because Laravel doesn't natively support enum alterations cleanly
        if (DB::connection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE marketplace_listings MODIFY COLUMN type ENUM('RENTAL', 'SALE', 'VEHICLE', 'ARTICLE') NOT NULL DEFAULT 'SALE'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back strictly to the original enum
        if (DB::connection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE marketplace_listings MODIFY COLUMN type ENUM('RENTAL', 'SALE') NOT NULL DEFAULT 'SALE'");
        }
    }
};
