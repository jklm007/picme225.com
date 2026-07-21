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
        Schema::table('provider_services', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_services', 'rental_driver_preference')) {
                $table->string('rental_driver_preference')->default('WITH_DRIVER');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_services', function (Blueprint $table) {
            if (Schema::hasColumn('provider_services', 'rental_driver_preference')) {
                $table->dropColumn('rental_driver_preference');
            }
        });
    }
};
