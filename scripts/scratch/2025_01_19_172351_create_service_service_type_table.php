<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceServiceTypeTable extends Migration
{
    public function up()
    {
        Schema::create('service_service_type', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id'); // BIGINT UNSIGNED
            $table->unsignedBigInteger('service_type_id'); // BIGINT UNSIGNED

            // Clés étrangères
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');

            // Clé primaire composée
            $table->primary(['service_id', 'service_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_service_type');
    }
}
