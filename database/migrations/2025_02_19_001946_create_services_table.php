<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); // Colonne `id` en BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
            $table->string('name'); // Nom du service
            $table->string('image')->nullable(); // Image du service (optionnelle)
            $table->timestamps(); // Colonnes `created_at` et `updated_at`
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
}
