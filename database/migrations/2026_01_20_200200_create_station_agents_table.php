<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStationAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_agents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Relation avec la table users
            $table->unsignedBigInteger('pdp_stop_id'); // Relation avec la gare
            $table->string('agent_code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pdp_stop_id')->references('id')->on('pdp_stops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('station_agents');
    }
}
