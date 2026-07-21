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
        Schema::create('eco_token_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('wallet_address');
            $table->enum('type', ['MINT', 'TRANSFER', 'BURN', 'REWARD', 'PAYMENT']);
            $table->decimal('amount', 20, 8);
            $table->string('transaction_hash')->nullable()->unique();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'FAILED'])->default('PENDING');
            $table->string('reference_type')->nullable()->comment('ride_booking, dao_vote, etc.');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('metadata')->nullable()->comment('JSON avec données supplémentaires');
            $table->integer('block_number')->nullable();
            $table->integer('confirmations')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'type', 'status']);
            $table->index('transaction_hash');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eco_token_transactions');
    }
};

