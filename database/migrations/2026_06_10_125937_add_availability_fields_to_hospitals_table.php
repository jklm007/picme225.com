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
        Schema::table('hospitals', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('longitude');
            $table->string('contact_phone', 50)->nullable()->after('is_available');
            $table->double('zone_coverage_radius_km', 5, 2)->default(15.00)->after('contact_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospitals', function (Blueprint $table) {
            $table->dropColumn([
                'is_available',
                'contact_phone',
                'zone_coverage_radius_km'
            ]);
        });
    }
};
