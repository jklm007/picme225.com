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
        // Antigravity: Update ENUM to include SOCIAL_PIC, SOCIAL_VID, SOCIAL_POST and previous definitions
        if (DB::connection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL', 'INTENTION', 'RENTAL', 'SALE', 'SOCIAL_PIC', 'SOCIAL_VID', 'SOCIAL_POST') NOT NULL DEFAULT 'SOCIAL'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the strictly previous enum
        if (DB::connection()->getDriverName() === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL') NOT NULL DEFAULT 'SOCIAL'");
        }
    }
};
