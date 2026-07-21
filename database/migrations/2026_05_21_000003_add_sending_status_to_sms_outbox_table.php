<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 'SENDING' to the status enum of sms_outbox table.
     * This value is used to lock rows during gateway polling and prevent double-sending.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `sms_outbox` MODIFY COLUMN `status` ENUM('PENDING','SENDING','SENT','FAILED') NOT NULL DEFAULT 'PENDING'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset SENDING rows to PENDING before removing the enum value
        DB::statement("UPDATE `sms_outbox` SET `status` = 'PENDING' WHERE `status` = 'SENDING'");
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `sms_outbox` MODIFY COLUMN `status` ENUM('PENDING','SENT','FAILED') NOT NULL DEFAULT 'PENDING'");
        }
    }
};
