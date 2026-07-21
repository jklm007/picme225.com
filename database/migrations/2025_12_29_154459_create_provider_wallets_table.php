<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->double('amount', 15, 8)->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('transaction_desc')->nullable();
            $table->enum('type', ['CREDIT', 'DEBIT']);
            $table->double('balance', 15, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_wallets');
    }
};
