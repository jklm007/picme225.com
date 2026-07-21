<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver_user', function (Blueprint $table) {
            $table->id();

            // Using foreignId() is the recommended and safest way in modern Laravel
            // It automatically creates a BIGINT UNSIGNED column and adds the foreign key constraint.
            $table->foreignId('driver_id')->constrained('providers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->double('pickup_latitude');
            $table->double('pickup_longitude');
            $table->double('destination_latitude');
            $table->double('destination_longitude');
            $table->boolean('dropped_off')->default(false);
            $table->timestamps();
            $table->engine = 'InnoDB'; // Ensure InnoDB engine

            $table->unique(['driver_id', 'user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_user');
    }
};
