<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFinancialModeToFleets extends Migration
{
    public function up()
    {
        if (Schema::hasTable('fleets')) {
            Schema::table('fleets', function (Blueprint $table) {
                if (!Schema::hasColumn('fleets', 'financial_mode')) {
                    $table->enum('financial_mode', ['MANAGED', 'AUTONOMOUS'])->default('MANAGED')->after('type');
                }
                if (!Schema::hasColumn('fleets', 'settlement_frequency')) {
                    $table->enum('settlement_frequency', ['DAILY', 'WEEKLY', 'MONTHLY'])->default('WEEKLY')->after('financial_mode');
                }
                if (!Schema::hasColumn('fleets', 'pending_settlement')) {
                    $table->decimal('pending_settlement', 10, 2)->default(0.00)->after('wallet_balance');
                }
            });
        }

        // Add settlement_status to cash_collections
        if (Schema::hasTable('cash_collections')) {
            Schema::table('cash_collections', function (Blueprint $table) {
                if (!Schema::hasColumn('cash_collections', 'settlement_status')) {
                    $table->enum('settlement_status', ['PENDING', 'AGENT_PAID', 'FLEET_PAID', 'PLATFORM_RECEIVED'])->default('PENDING')->after('reconciled');
                }
            });
        }

        // Create settlements table for tracking platform payments
        if (!Schema::hasTable('fleet_settlements')) {
            Schema::create('fleet_settlements', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('fleet_id');
                $table->decimal('amount', 10, 2);
                $table->enum('type', ['CASH_DEPOSIT', 'COMMISSION_PAYOUT', 'REFUND']);
                $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED']);
                $table->string('payment_method')->nullable(); // MOBILE_MONEY, BANK_TRANSFER
                $table->string('payment_reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('fleet_settlements');

        if (Schema::hasTable('cash_collections')) {
            Schema::table('cash_collections', function (Blueprint $table) {
                $table->dropColumn('settlement_status');
            });
        }

        if (Schema::hasTable('fleets')) {
            Schema::table('fleets', function (Blueprint $table) {
                $table->dropColumn(['financial_mode', 'settlement_frequency', 'pending_settlement']);
            });
        }
    }
}
