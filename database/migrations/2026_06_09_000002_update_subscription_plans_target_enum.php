<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Rename the 'user' target value to 'marketplace' in subscription_plans.
 *
 * Before : target ENUM('provider', 'user')
 * After  : target ENUM('provider', 'marketplace')
 *
 * All existing 'user' plans (WORK PASS, SCHOOL PASS, etc.) are renamed to
 * 'marketplace' because they are fixed-price plans sold to end-users on the
 * marketplace — NOT dynamic transport scheduling plans.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — Temporarily change column type to VARCHAR to allow the rename
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN target VARCHAR(20) NOT NULL DEFAULT 'provider'");
        }

        // Step 2 — Rename existing 'user' records to 'marketplace'
        DB::table('subscription_plans')
            ->where('target', 'user')
            ->update(['target' => 'marketplace']);

        // Step 3 — Restore proper ENUM with new values
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN target ENUM('provider', 'marketplace') NOT NULL DEFAULT 'provider'");
        }
    }

    public function down(): void
    {
        // Step 1 — Temporarily change column type to VARCHAR
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN target VARCHAR(20) NOT NULL DEFAULT 'provider'");
        }

        // Step 2 — Rename 'marketplace' back to 'user'
        DB::table('subscription_plans')
            ->where('target', 'marketplace')
            ->update(['target' => 'user']);

        // Step 3 — Restore original ENUM
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN target ENUM('provider', 'user') NOT NULL DEFAULT 'provider'");
        }
    }
};
