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
        Schema::create('ride_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('active_shared_ride_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('start_stop_id')->comment('Arrêt de départ');
            $table->unsignedBigInteger('end_stop_id')->comment('Arrêt d\'arrivée');
            $table->integer('seats_booked')->default(1);
            $table->decimal('price', 10, 2);
            $table->decimal('detour_distance', 8, 2)->nullable()->comment('Distance de détour en km si porte-à-porte');
            $table->decimal('detour_price', 10, 2)->default(0)->comment('Prix supplémentaire pour le détour');
            $table->enum('status', ['CONFIRMED', 'BOARDED', 'COMPLETED', 'CANCELLED'])->default('CONFIRMED');
            $table->enum('payment_mode', ['CASH', 'CARD', 'PAYPAL', 'WALLET'])->default('CASH');
            $table->boolean('paid')->default(false);
            $table->boolean('use_wallet')->default(false);
            $table->timestamp('boarded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->foreign('active_shared_ride_id')->references('id')->on('active_shared_rides')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('start_stop_id')->references('id')->on('pdp_stops')->onDelete('restrict');
            $table->foreign('end_stop_id')->references('id')->on('pdp_stops')->onDelete('restrict');
            $table->index(['active_shared_ride_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_bookings');
    }
};

