<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletToFleetsAndCreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add balance to fleets table
        Schema::table('fleets', function (Blueprint $table) {
            if (!Schema::hasColumn('fleets', 'wallet_balance')) {
                $table->double('wallet_balance', 15, 2)->default(0)->after('mobile');
            }
        });

        // 2. Create fleet_wallets table for transaction history
        Schema::create('fleet_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fleet_id');
            $table->double('amount', 15, 2);
            $table->string('transaction_id')->nullable();
            $table->string('transaction_desc')->nullable();
            $table->enum('type', ['CREDIT', 'DEBIT']);
            $table->double('balance', 15, 2);
            $table->timestamps();

            $table->foreign('fleet_id')->references('id')->on('fleets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fleet_wallets');
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropColumn('wallet_balance');
        });
    }
}
