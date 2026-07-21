<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les champs nécessaires pour la fonctionnalité de partage (DYNAMIC_POOL et PDP)
     * et les restrictions géographiques par commune
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            // Champs pour le service partagé
            $table->decimal('max_detour', 8, 2)->nullable()->after('sharing_type')
                  ->comment('Détour maximum autorisé en km pour porte-à-porte');
            
            $table->integer('max_waiting_time')->nullable()->after('max_detour')
                  ->comment('Temps d\'attente maximum en minutes');
            
            $table->decimal('detour_price_per_km', 8, 2)->nullable()->after('max_waiting_time')
                  ->comment('Prix par km pour les détours porte-à-porte');
            
            // Restriction géographique
            $table->string('commune', 100)->nullable()->after('detour_price_per_km')
                  ->comment('Commune principale où le service est disponible (null = toutes)');
            
            $table->json('communes')->nullable()->after('commune')
                  ->comment('Liste des communes autorisées (pour multi-communes)');
            
            // Index pour améliorer les performances de recherche
            $table->index('commune', 'idx_service_types_commune');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            // Supprimer l'index d'abord
            $table->dropIndex('idx_service_types_commune');
            
            // Puis supprimer les colonnes
            $table->dropColumn([
                'max_detour',
                'max_waiting_time',
                'detour_price_per_km',
                'commune',
                'communes'
            ]);
        });
    }
};
