<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned(); 
            $table->string('name');
            $table->string('provider_name')->nullable();
            $table->string('image')->nullable();
            $table->integer('capacity')->default(0);
            $table->integer('fixed');
            $table->integer('price');
            $table->integer('minute');
            $table->integer('hour')->nullable();
            $table->integer('distance');
            $table->enum('calculator', ['MIN', 'HOUR', 'DISTANCE', 'DISTANCEMIN', 'DISTANCEDAY']);
            $table->string('description')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        // Re-create Foreign Key Constraint AFTER modifying service_types.id
//        Schema::table('service_service_type', function (Blueprint $table) {
  //          $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade'); 
    //    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop Foreign Key Constraint BEFORE dropping service_types.id
  //      Schema::table('service_service_type', function (Blueprint $table) {
//            $table->dropForeign(['service_type_id']); // Drop foreign key constraint first
    //    });


        Schema::dropIfExists('service_types');
    }
}
