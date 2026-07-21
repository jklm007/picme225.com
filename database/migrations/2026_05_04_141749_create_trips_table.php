<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $description) {
            $description->id();
            $description->unsignedBigInteger('user_id'); // Conducteur
            $description->string('origin_name');
            $description->string('destination_name');
            $description->decimal('origin_lat', 10, 8);
            $description->decimal('origin_lng', 11, 8);
            $description->decimal('destination_lat', 10, 8);
            $description->decimal('destination_lng', 11, 8);
            $description->dateTime('departure_time');
            $description->integer('seats_available');
            $description->integer('price')->default(0);
            $description->text('description')->nullable();
            $description->enum('status', ['OPEN', 'FULL', 'STARTED', 'COMPLETED', 'CANCELLED'])->default('OPEN');
            $description->unsignedBigInteger('pdp_route_id')->nullable(); // Corridor optionnel
            $description->timestamps();
            $description->softDeletes();

            $description->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
