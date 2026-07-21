<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Change social_points from integer to decimal to support micro-rewards.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('social_points', 12, 2)->default(0)->change();
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->decimal('social_points', 12, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('social_points')->default(0)->change();
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->integer('social_points')->default(0)->change();
        });
    }
};
