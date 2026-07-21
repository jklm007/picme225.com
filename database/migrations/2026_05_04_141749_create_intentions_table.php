<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Passager
            $table->string('origin_name');
            $table->string('destination_name');
            $table->decimal('origin_lat', 10, 8);
            $table->decimal('origin_lng', 11, 8);
            $table->decimal('destination_lat', 10, 8);
            $table->decimal('destination_lng', 11, 8);
            $table->dateTime('earliest_departure');
            $table->dateTime('latest_departure');
            $table->integer('seats_needed')->default(1);
            $table->integer('budget_max')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['PENDING', 'MATCHED', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->unsignedBigInteger('pdp_route_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentions');
    }
};
