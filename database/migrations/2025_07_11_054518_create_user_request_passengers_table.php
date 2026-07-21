<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestPassengersTable extends Migration
{
    public function up()
    {
        Schema::create('user_request_passengers', function (Blueprint $table) {
            $table->id();

            // --- CORRECTION ---
            // Au lieu de foreignId(), on définit le type manuellement pour correspondre à user_requests.id
            $table->unsignedInteger('user_request_id'); 
            $table->foreign('user_request_id')
                  ->references('id')->on('user_requests')
                  ->onDelete('cascade');

            // Pour user_id, supposons que users.id est BIGINT UNSIGNED (créé par $table->id())
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->integer('passengers_count')->default(1);
            $table->enum('segment_type', ['passenger', 'delivery'])->default('passenger');

            // Pour pdp_id, supposons que pdp_stops.id est BIGINT UNSIGNED (créé par $table->id())
            $table->foreignId('pickup_pdp_id')->nullable()->constrained('pdp_stops')->onDelete('set null');
            $table->foreignId('dropoff_pdp_id')->nullable()->constrained('pdp_stops')->onDelete('set null');

            $table->enum('status', ['PENDING', 'ACCEPTED', 'PICKED_UP', 'DROPPED', 'CANCELLED'])->default('PENDING');
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_request_passengers');
    }
}
