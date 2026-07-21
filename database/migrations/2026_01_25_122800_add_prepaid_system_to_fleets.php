<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrepaidSystemToFleets extends Migration
{
    public function up()
    {
        if (Schema::hasTable('fleets')) {
            Schema::table('fleets', function (Blueprint $table) {
                if (!Schema::hasColumn('fleets', 'prepaid_balance')) {
                    $table->decimal('prepaid_balance', 10, 2)->default(0.00)->after('wallet_balance');
                }
                if (!Schema::hasColumn('fleets', 'prepaid_threshold')) {
                    $table->decimal('prepaid_threshold', 10, 2)->default(10000.00)->after('prepaid_balance');
                }
                if (!Schema::hasColumn('fleets', 'auto_recharge_enabled')) {
                    $table->boolean('auto_recharge_enabled')->default(false)->after('prepaid_threshold');
                }
                if (!Schema::hasColumn('fleets', 'auto_recharge_amount')) {
                    $table->decimal('auto_recharge_amount', 10, 2)->default(50000.00)->after('auto_recharge_enabled');
                }
            });
        }

        // Create prepaid transactions log
        if (!Schema::hasTable('fleet_prepaid_transactions')) {
            Schema::create('fleet_prepaid_transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('fleet_id');
                $table->enum('type', ['RECHARGE', 'DEDUCTION', 'REFUND', 'ADJUSTMENT']);
                $table->decimal('amount', 10, 2);
                $table->decimal('balance_before', 10, 2);
                $table->decimal('balance_after', 10, 2);
                $table->string('reference')->nullable(); // Booking ID, Payment ID, etc.
                $table->text('description')->nullable();
                $table->string('payment_method')->nullable(); // For RECHARGE
                $table->string('payment_reference')->nullable();
                $table->timestamps();

                $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('fleet_prepaid_transactions');

        if (Schema::hasTable('fleets')) {
            Schema::table('fleets', function (Blueprint $table) {
                $table->dropColumn([
                    'prepaid_balance',
                    'prepaid_threshold',
                    'auto_recharge_enabled',
                    'auto_recharge_amount'
                ]);
            });
        }
    }
}
