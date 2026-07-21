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
        if (!Schema::hasTable('gateway_nodes')) {
            Schema::create('gateway_nodes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone_number')->unique();
                $table->string('network')->comment('WAVE, ORANGE, MTN, MOOV');
                $table->enum('type', ['RECEIVER', 'PAYOUT', 'VAULT', 'PROFIT'])->default('RECEIVER');
                $table->enum('status', ['ACTIVE', 'INACTIVE', 'LIMIT_REACHED'])->default('ACTIVE');
                
                $table->decimal('current_balance', 15, 2)->default(0);
                $table->decimal('daily_volume', 15, 2)->default(0);
                $table->decimal('monthly_volume', 15, 2)->default(0);
                
                $table->decimal('daily_limit', 15, 2)->default(2000000);
                $table->decimal('monthly_limit', 15, 2)->default(10000000);
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gateway_nodes');
    }
};
