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
        Schema::table('eco_token_transactions', function (Blueprint $table) {
            // Change enum to string to support more flexible types and status (e.g., COMPLETED, CASH_COMMISSION_DEDUCTION)
            $table->string('type')->change();
            $table->string('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eco_token_transactions', function (Blueprint $table) {
            $table->enum('type', ['MINT', 'TRANSFER', 'BURN', 'REWARD', 'PAYMENT'])->change();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'FAILED'])->change();
        });
    }
};
