<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enhance user_subscription_schedules for dynamic VTC subscriptions.
 *
 * New columns:
 *  - distance_km   : OSRM road distance (replaces Haversine estimate)
 *  - duration_mins : OSRM road duration in minutes
 *  - expires_at    : Expiry of the paid subscription period (e.g. +30 days)
 *  - payment_mode  : WALLET (default) or CASH
 *  - notes         : Free-text field for admin/user notes
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscription_schedules', function (Blueprint $table) {
            // OSRM routing results (replaces the imprecise Haversine calculation)
            $table->decimal('distance_km', 8, 2)
                  ->nullable()
                  ->after('monthly_price')
                  ->comment('Road distance in km calculated by OSRM router');

            $table->integer('duration_mins')
                  ->nullable()
                  ->after('distance_km')
                  ->comment('Estimated road duration in minutes calculated by OSRM');

            // Subscription validity period
            $table->dateTime('expires_at')
                  ->nullable()
                  ->after('duration_mins')
                  ->comment('Date after which this schedule should be set to EXPIRED');

            // Payment mode for generated rides
            $table->string('payment_mode', 20)
                  ->default('WALLET')
                  ->after('expires_at')
                  ->comment('WALLET = pre-paid via subscription | CASH = pay on delivery');

            // Optional notes
            $table->text('notes')->nullable()->after('payment_mode');

            // Index for faster cron job queries
            $table->index(['status', 'expires_at'], 'idx_schedule_status_expires');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscription_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedule_status_expires');
            $table->dropColumn([
                'distance_km',
                'duration_mins',
                'expires_at',
                'payment_mode',
                'notes',
            ]);
        });
    }
};
