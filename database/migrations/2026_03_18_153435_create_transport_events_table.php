<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id'); // Created by fleet owner / driver
            $table->integer('service_type_id')->nullable(); // Link to service type
            $table->integer('pdp_route_id')->nullable(); // Optional corridor
            
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('s_address');
            $table->double('s_latitude', 15, 8);
            $table->double('s_longitude', 15, 8);
            
            $table->string('d_address');
            $table->double('d_latitude', 15, 8);
            $table->double('d_longitude', 15, 8);
            
            $table->dateTime('departure_time');
            $table->double('price', 10, 2)->default(0);
            
            $table->integer('total_seats')->default(1);
            $table->integer('available_seats')->default(1);
            
            $table->string('status')->default('SCHEDULED'); // SCHEDULED, BOARDING, STARTED, COMPLETED, CANCELLED
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transport_events');
    }
}
