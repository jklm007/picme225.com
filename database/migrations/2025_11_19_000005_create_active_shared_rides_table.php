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
        Schema::create('active_shared_rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_route_id');
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('vehicle_id')->nullable()->comment('ID du véhicule depuis la table fleets ou providers');
            $table->enum('status', ['EN_ROUTE', 'TERMINATED', 'CANCELLED'])->default('EN_ROUTE');
            $table->integer('available_seats')->default(0);
            $table->integer('total_seats')->default(0);
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->unsignedBigInteger('next_stop_id')->nullable()->comment('Prochain arrêt prévu');
            $table->unsignedBigInteger('current_stop_id')->nullable()->comment('Arrêt actuel');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('last_position_update')->nullable();
            $table->timestamps();

            $table->foreign('pdp_route_id')->references('id')->on('pdp_routes')->onDelete('cascade');
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('next_stop_id')->references('id')->on('pdp_stops')->onDelete('set null');
            $table->foreign('current_stop_id')->references('id')->on('pdp_stops')->onDelete('set null');
            $table->index(['status', 'pdp_route_id']);
            $table->index(['current_latitude', 'current_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_shared_rides');
    }
};

