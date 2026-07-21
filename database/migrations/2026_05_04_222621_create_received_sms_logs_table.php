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
        Schema::create('received_sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('sender')->nullable();
            $table->text('message')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('status', ['UNCLAIMED', 'CLAIMED', 'IGNORED'])->default('UNCLAIMED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_sms_logs');
    }
};
