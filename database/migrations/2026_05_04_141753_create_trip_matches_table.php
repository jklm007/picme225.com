<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('intention_id');
            $table->integer('score'); // Score de matching (0-100)
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'BOOKED'])->default('PENDING');
            $table->timestamps();

            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('intention_id')->references('id')->on('intentions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_matches');
    }
};
