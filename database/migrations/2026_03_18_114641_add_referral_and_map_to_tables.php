<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute le système de parrainage et le support cartographique pour le flux social.
     */
    public function up(): void
    {
        // 1. Système de Parrainage (Referral)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_unique_id')) {
                $table->string('referral_unique_id', 20)->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'referred_by_id')) {
                $table->unsignedInteger('referred_by_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'referral_count')) {
                $table->integer('referral_count')->default(0);
            }
        });

        // 2. Coordonnées pour le Social Map Mode
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('posts', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('posts', 'address')) {
                $table->string('address')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['referral_unique_id', 'referred_by_id', 'referral_count']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'address']);
        });
    }
};
