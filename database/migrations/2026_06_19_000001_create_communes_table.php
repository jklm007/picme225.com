<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunesTable extends Migration
{
    public function up()
    {
        if (!Schema::connection('pgsql')->hasTable('communes')) {
            Schema::connection('pgsql')->create('communes', function (Blueprint $table) {
                $table->id();
                $table->string('pays')->default('Cote d Ivoire');
                $table->string('ville')->default('Abidjan');
                $table->string('commune');
                $table->string('code_commune')->nullable()->unique();
                
                // Centre de la commune
                $table->decimal('latitude_centre', 10, 8)->nullable();
                $table->decimal('longitude_centre', 11, 8)->nullable();
                
                // Polygone géographique (PostGIS GEOMETRY)
                $table->polygon('polygone_zone')->nullable();
                
                $table->enum('statut', ['actif', 'inactif', 'en_preparation'])->default('actif');
                $table->timestamps();
            });
        }
        
        // Ajouter un index spatial GIST
        // DB::statement('CREATE INDEX communes_polygone_zone_gist ON communes USING GIST (polygone_zone);');
    }

    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('communes');
    }
}
