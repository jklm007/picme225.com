<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('user_requests', 'rental_start_date')) {
            Schema::table('user_requests', function (Blueprint $table) {
                $table->dateTime('rental_start_date')->nullable()->after('rental_hours');
                $table->dateTime('rental_end_date')->nullable()->after('rental_start_date');
                $table->boolean('rental_with_driver')->default(true)->after('rental_end_date');
                $table->unsignedBigInteger('hospital_id')->nullable()->after('rental_with_driver');
            });
        }

        if (!Schema::hasColumn('hospitals', 'is_available')) {
            Schema::table('hospitals', function (Blueprint $table) {
                $table->boolean('is_available')->default(true);
                $table->string('contact_phone', 50)->nullable();
                $table->double('zone_coverage_radius_km', 5, 2)->default(15.00);
            });
        }
    }

    public function down(): void
    {
        // ...
    }
};
