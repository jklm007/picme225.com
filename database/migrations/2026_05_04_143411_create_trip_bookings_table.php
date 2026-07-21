<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('user_id'); // Passager
            $table->integer('seats_booked')->default(1);
            $table->decimal('price', 10, 2);
            $table->string('handshake_code')->unique();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'BOARDED', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->enum('payment_status', ['UNPAID', 'ESCROW', 'PAID', 'REFUNDED'])->default('UNPAID');
            $table->string('payment_mode')->default('WALLET');
            $table->dateTime('boarded_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_bookings');
    }
};
