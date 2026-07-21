<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add waypoints (stops) column to user_subscription_schedules table.
 * This allows commute subscriptions to include intermediate stop points,
 * which will be factored into the distance/price calculation via OSRM.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscription_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('user_subscription_schedules', 'waypoints')) {
                $table->json('waypoints')->nullable()->after('d_lng')
                    ->comment('Array of intermediate stop points [{address, latitude, longitude}]');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_subscription_schedules', function (Blueprint $table) {
            $table->dropColumn('waypoints');
        });
    }
};
