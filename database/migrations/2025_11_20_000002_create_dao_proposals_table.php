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
        Schema::create('dao_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('blockchain_proposal_id')->unique()->comment('ID de la proposition sur la blockchain');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['PRICE_CHANGE', 'ROUTE_ADDITION', 'ROUTE_MODIFICATION', 'PARAMETER_CHANGE']);
            $table->string('title');
            $table->text('description');
            $table->json('execution_data')->nullable()->comment('Données pour l\'exécution de la proposition');
            $table->enum('status', ['PENDING', 'ACTIVE', 'PASSED', 'REJECTED', 'EXECUTED'])->default('PENDING');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->bigInteger('votes_for')->default(0);
            $table->bigInteger('votes_against')->default(0);
            $table->bigInteger('votes_abstain')->default(0);
            $table->boolean('executed')->default(false);
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dao_proposals');
    }
};

