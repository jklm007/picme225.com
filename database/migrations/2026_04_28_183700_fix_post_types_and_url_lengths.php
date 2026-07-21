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
        // 1. Mise à jour de l'ENUM pour inclure RSS_NEWS
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL', 'INTENTION', 'RENTAL', 'SALE', 'SOCIAL_PIC', 'SOCIAL_VID', 'SOCIAL_POST', 'ROAD_INFO', 'RSS_NEWS') NOT NULL DEFAULT 'SOCIAL'");
        }

        // 2. Augmentation de la taille des colonnes URL (String 255 -> Text)
        Schema::table('posts', function (Blueprint $table) {
            $table->text('external_link')->nullable()->change();
            $table->text('media_url')->nullable()->change();
            $table->string('source')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('external_link')->nullable()->change();
            $table->string('media_url')->nullable()->change();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('TRIP', 'NEWS', 'VIRAL', 'POLL', 'SOCIAL', 'INTENTION', 'RENTAL', 'SALE', 'SOCIAL_PIC', 'SOCIAL_VID', 'SOCIAL_POST', 'ROAD_INFO') NOT NULL DEFAULT 'SOCIAL'");
        }
    }
};
