<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuartiersTable extends Migration
{
    public function up()
    {
        if (!Schema::connection('pgsql')->hasTable('quartiers')) {
            Schema::connection('pgsql')->create('quartiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('commune_id');
                $table->string('nom_quartier');
                
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                
                // Polygone géographique (PostGIS GEOMETRY)
                $table->polygon('zone_geo')->nullable();
                
                $table->enum('statut', ['actif', 'inactif'])->default('actif');
                $table->timestamps();
                
                $table->foreign('commune_id')->references('id')->on('communes')->onDelete('cascade');
            });
        }
        
        // Index spatial
        // DB::statement('CREATE INDEX quartiers_zone_geo_gist ON quartiers USING GIST (zone_geo);');
    }

    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('quartiers');
    }
}
