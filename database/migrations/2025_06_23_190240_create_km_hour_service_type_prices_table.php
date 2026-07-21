<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_km_hour_service_type_prices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKmHourServiceTypePricesTable extends Migration
{
    public function up()
    {
        Schema::create('km_hour_service_type_prices', function (Blueprint $table) {
            $table->id(); // Crée un BIGINT UNSIGNED 'id'

            // Pour km_hours.id qui est INT(10) UNSIGNED
            $table->unsignedInteger('km_hour_id');
            $table->foreign('km_hour_id')
                  ->references('id')->on('km_hours')
                  ->onDelete('cascade');

            // Pour service_types.id, si c'est BIGINT UNSIGNED (créé par $table->id())
            $table->unsignedBigInteger('service_type_id');
            $table->foreign('service_type_id')
                  ->references('id')->on('service_types')
                  ->onDelete('cascade');

            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['km_hour_id', 'service_type_id'], 'km_hour_service_type_unique_price');
        });
    }

    public function down()
    {
        Schema::table('km_hour_service_type_prices', function (Blueprint $table) {
            // Laravel génère des noms comme table_column_foreign
            // Pour plus de robustesse, vous pouvez nommer vos contraintes
            // $table->dropForeign('km_hour_service_type_prices_km_hour_id_foreign');
            // $table->dropForeign('km_hour_service_type_prices_service_type_id_foreign');
            // Ou plus simple si Laravel s'en occupe bien:
            $table->dropForeign(['km_hour_id']);
            $table->dropForeign(['service_type_id']);
        });
        Schema::dropIfExists('km_hour_service_type_prices');
    }
}
