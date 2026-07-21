<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add columns that exist in the ServiceType model but are missing from the DB.
     * Root cause: these fields were added to the model's $fillable without a matching migration.
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'free_km_per_passenger')) {
                $table->integer('free_km_per_passenger')->default(0)->after('sharing_type');
            }
            if (!Schema::hasColumn('service_types', 'max_detour')) {
                $table->float('max_detour')->nullable()->after('free_km_per_passenger');
            }
            if (!Schema::hasColumn('service_types', 'max_waiting_time')) {
                $table->integer('max_waiting_time')->nullable()->after('max_detour');
            }
            if (!Schema::hasColumn('service_types', 'detour_price_per_km')) {
                $table->float('detour_price_per_km')->nullable()->after('max_waiting_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn([
                'free_km_per_passenger',
                'max_detour',
                'max_waiting_time',
                'detour_price_per_km',
            ]);
        });
    }
};
