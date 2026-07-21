<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // On modifie l'enum de la colonne 'type' pour inclure INTENTION, RENTAL, SALE, SOCIAL_PIC, SOCIAL_VID
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL', 'INTENTION', 'RENTAL', 'SALE', 'SOCIAL_PIC', 'SOCIAL_VID') DEFAULT 'SOCIAL'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL') DEFAULT 'SOCIAL'");
        }
    }
};
