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
        Schema::create('dao_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proposal_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_wallet_address');
            $table->enum('vote', ['FOR', 'AGAINST', 'ABSTAIN']);
            $table->decimal('token_amount', 20, 8)->comment('Nombre de tokens utilisés pour voter');
            $table->string('transaction_hash')->nullable();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'FAILED'])->default('PENDING');
            $table->timestamps();

            $table->foreign('proposal_id')->references('id')->on('dao_proposals')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['proposal_id', 'user_id'], 'unique_proposal_user_vote');
            $table->index('transaction_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dao_votes');
    }
};

