<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePdpStopsTable extends Migration
{
    public function up()
    {
        if (!Schema::connection('pgsql')->hasTable('pdp_stops')) {
            Schema::connection('pgsql')->create('pdp_stops', function (Blueprint $table) {
                $table->id();
                $table->string('nom_arret');
                $table->enum('type_arret', ['communal', 'prive', 'gare', 'carrefour', 'commerce', 'lieu_public'])->default('carrefour');
                
                $table->unsignedBigInteger('commune_id')->nullable();
                $table->unsignedBigInteger('quartier_id')->nullable();
                
                $table->string('adresse')->nullable();
                $table->text('description')->nullable();
                
                // PostGIS GEOMETRY/POINT
                $table->point('location');
                $table->decimal('latitude', 10, 8)->nullable(); // Optionnel si on utilise seulement location, mais pratique
                $table->decimal('longitude', 11, 8)->nullable();
                
                $table->integer('rayon_validation_metre')->default(50);
                $table->string('precision_gps')->nullable();
                
                $table->enum('source_coordonnees', ['photon', 'admin', 'gps', 'import'])->default('admin');
                $table->string('photon_place_id')->nullable();
                $table->json('photon_raw_data')->nullable();
                
                $table->boolean('ors_verified')->default(false);
                $table->enum('statut_validation', ['automatique', 'en_attente', 'manuel', 'rejete'])->default('manuel');
                $table->integer('confidence_score')->default(0); // 0 à 100
                
                $table->timestamps();
                
                $table->foreign('commune_id')->references('id')->on('communes')->onDelete('set null');
                $table->foreign('quartier_id')->references('id')->on('quartiers')->onDelete('set null');
            });
        }
        
        // Index spatial pour la localisation
        // DB::statement('CREATE INDEX pdp_stops_location_gist ON pdp_stops USING GIST (location);');
    }

    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('pdp_stops');
    }
}
