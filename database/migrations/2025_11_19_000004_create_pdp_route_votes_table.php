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
        Schema::create('pdp_route_votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_route_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('vote', ['YES', 'NO'])->default('YES');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('pdp_route_id')->references('id')->on('pdp_routes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['pdp_route_id', 'user_id'], 'unique_route_user_vote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdp_route_votes');
    }
};

