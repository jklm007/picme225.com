<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceServiceTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_service_type', function (Blueprint $table) {
            // Clés étrangères
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('service_type_id');

            // Colonnes supplémentaires pour la table pivot
          
            $table->string('name');
            $table->string('provider_name')->nullable();
            $table->string('image')->nullable();
            $table->integer('capacity')->default(0);
            $table->integer('fixed');
            $table->integer('price');
            $table->integer('minute');
            $table->integer('hour')->nullable();
            $table->integer('distance');
            $table->string('day')->nullable();
            $table->enum('calculator', ['MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEDAY']);
            $table->string('description')->nullable();
            $table->integer('status')->default(0);
            $table->boolean('ambulance')->default(0);
            $table->decimal('rental_amount', 8, 2)->nullable();
            $table->decimal('outstation_price', 8, 2)->nullable();

            // Timestamps
            $table->timestamps();

            // Clés primaires composées
            $table->primary(['service_id', 'service_type_id']);

            // Contraintes de clés étrangères (DÉCOMMENTÉES et CORRECTES)
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_service_type');
    }
}
