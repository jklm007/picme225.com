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
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->boolean('is_synced_to_chain')->default(false)->after('dao_treasury_fee');
            $table->string('blockchain_tx_hash')->nullable()->after('is_synced_to_chain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn(['is_synced_to_chain', 'blockchain_tx_hash']);
        });
    }
};
