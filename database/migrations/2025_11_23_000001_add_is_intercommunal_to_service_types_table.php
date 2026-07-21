<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute le champ is_intercommunal à service_types pour indiquer
     * si un service traverse plusieurs communes ou est limité à une seule.
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            // Indique si le service traverse plusieurs communes
            $table->boolean('is_intercommunal')
                  ->default(false)
                  ->after('communes')
                  ->comment('Service intercommunal (traverse plusieurs communes)');
            
            // Index pour améliorer les performances de filtrage
            $table->index('is_intercommunal', 'idx_service_types_intercommunal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            // Supprimer l'index d'abord
            $table->dropIndex('idx_service_types_intercommunal');
            
            // Puis supprimer la colonne
            $table->dropColumn('is_intercommunal');
        });
    }
};
