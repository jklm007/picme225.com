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
        Schema::create('chat_quotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('listing_id')->nullable();
            $table->double('amount', 10, 2);
            $table->string('status', 50)->default('PENDING'); // PENDING, ACCEPTED, REJECTED, CANCELED
            $table->timestamps();

            // Foreign keys if necessary (we can just index them for performance)
            $table->index('message_id');
            $table->index('provider_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_quotes');
    }
};
