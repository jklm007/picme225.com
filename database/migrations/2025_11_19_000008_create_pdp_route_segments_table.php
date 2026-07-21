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
        Schema::create('pdp_route_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_route_id');
            $table->unsignedBigInteger('from_stop_id')->comment('Arrêt de départ du segment');
            $table->unsignedBigInteger('to_stop_id')->comment('Arrêt d\'arrivée du segment');
            $table->integer('order')->comment('Ordre du segment dans l\'itinéraire');
            $table->decimal('price', 10, 2)->default(200)->comment('Prix fixe du segment (base: 200 FCFA)');
            $table->decimal('distance_km', 8, 2)->nullable()->comment('Distance du segment en km');
            $table->string('commune')->nullable()->comment('Commune du segment');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('pdp_route_id')->references('id')->on('pdp_routes')->onDelete('cascade');
            $table->foreign('from_stop_id')->references('id')->on('pdp_stops')->onDelete('cascade');
            $table->foreign('to_stop_id')->references('id')->on('pdp_stops')->onDelete('cascade');
            $table->unique(['pdp_route_id', 'from_stop_id', 'to_stop_id'], 'unique_route_segment');
            $table->index(['pdp_route_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdp_route_segments');
    }
};

