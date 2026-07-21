<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fleet_id');
            $table->double('amount', 15, 2);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->string('method')->default('MOBILE_MONEY');
            $table->string('account_number'); // The phone number for Mobile Money
            $table->string('recipient_name')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('transaction_id')->nullable(); // External reference from MM provider
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
        Schema::dropIfExists('withdrawals');
    }
}
