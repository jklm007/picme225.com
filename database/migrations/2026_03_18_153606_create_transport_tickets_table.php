<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transport_event_id');
            $table->integer('user_id'); // Passenger
            $table->string('qr_code')->unique(); // E-ticket
            $table->integer('seats_booked')->default(1);
            $table->double('total_price', 10, 2);
            $table->string('payment_status')->default('PENDING'); // PENDING, PAID
            $table->string('payment_mode')->default('WALLET'); // WALLET, CASH, MOOV
            $table->string('status')->default('BOOKED'); // BOOKED, USED, CANCELLED
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transport_tickets');
    }
}
