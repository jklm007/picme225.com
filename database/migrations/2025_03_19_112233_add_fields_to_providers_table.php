<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            // Ajoute la colonne 'commune' si elle n'existe pas déjà.
            // C'est la création initiale, elle est donc nullable.
            // On utilise 1000 caractères pour la longueur, comme dans vos précédentes tentatives.
            if (!Schema::hasColumn('providers', 'commune')) {
                $table->string('commune', 1000)->nullable()->after('longitude');
            }

            // Colonne 'available' - Ajout conditionnel
            if (!Schema::hasColumn('providers', 'available')) {
                $table->boolean('available')->after('longitude')->default(false);
            }

            // Colonne 'service_type_id' - Ajout conditionnel et correction de la contrainte
            if (!Schema::hasColumn('providers', 'service_type_id')) {
                $table->foreignId('service_type_id')->after('available')->nullable()->constrained('service_types')->onDelete('set null');
            }

            // Colonne 'passengers_count' - Ajout conditionnel
            if (!Schema::hasColumn('providers', 'passengers_count')) {
                $table->integer('passengers_count')->after('service_type_id')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            // On supprime les colonnes seulement si elles existent, et dans le bon ordre pour les clés étrangères.

            // Suppression de la colonne 'commune'
            if (Schema::hasColumn('providers', 'commune')) {
                $table->dropColumn('commune');
            }

            // Suppression de la contrainte de clé étrangère 'service_type_id' en premier
            if (Schema::hasColumn('providers', 'service_type_id')) {
                // Utilisez dropForeign pour supprimer la contrainte par son nom généré par Laravel
                // Ou $table->dropConstrainedForeignId('service_type_id'); si vous êtes sur une version récente de Laravel
                $table->dropForeign(['service_type_id']); // Supprime la clé étrangère
            }

            // Suppression des colonnes restantes
            if (Schema::hasColumn('providers', 'available')) {
                $table->dropColumn('available');
            }
            if (Schema::hasColumn('providers', 'service_type_id')) { // Maintenant, supprime la colonne elle-même
                $table->dropColumn('service_type_id');
            }
            if (Schema::hasColumn('providers', 'passengers_count')) {
                $table->dropColumn('passengers_count');
            }
        });
    }
};
