<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add marketplace subscription columns to users table.
 *
 * Separates marketplace subscriptions (fixed-price) from transport
 * scheduling (dynamic-price). Each user can now hold BOTH simultaneously:
 *  - marketplace_plan_id      → Starter / Pro / Business boutique plan
 *  - subscription_plan_id     → (kept for backward compat, driver plans)
 *  - UserSubscriptionSchedule → Dynamic VTC commute scheduling
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Marketplace subscription (fixed price plan for sellers/merchants)
            $table->unsignedBigInteger('marketplace_plan_id')
                  ->nullable()
                  ->after('subscription_expires_at')
                  ->comment('FK to subscription_plans (target=marketplace)');

            $table->dateTime('marketplace_plan_expires_at')
                  ->nullable()
                  ->after('marketplace_plan_id')
                  ->comment('Expiry date of the active marketplace subscription');

            // Foreign key — soft: if plan is deleted, set null
            $table->foreign('marketplace_plan_id')
                  ->references('id')
                  ->on('subscription_plans')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['marketplace_plan_id']);
            $table->dropColumn(['marketplace_plan_id', 'marketplace_plan_expires_at']);
        });
    }
};
