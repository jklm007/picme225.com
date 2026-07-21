<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Change gateway_node_id from unsignedInteger to varchar(50)
     * to allow string node identifiers like "GW-NODE-01" or phone numbers.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `sms_outbox` MODIFY COLUMN `gateway_node_id` VARCHAR(50) NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        // Reset non-numeric values before converting back
        DB::statement("UPDATE `sms_outbox` SET `gateway_node_id` = NULL WHERE `gateway_node_id` REGEXP '[^0-9]'");
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `sms_outbox` MODIFY COLUMN `gateway_node_id` INT UNSIGNED NULL DEFAULT NULL");
        }
    }
};
