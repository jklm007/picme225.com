<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les colonnes manquantes dans transport_tickets :
     * - listing_id : lien vers l'annonce marketplace
     * - event_pass_type_id : lien vers le type de pass (billetterie)
     * - metadata : JSON flexible pour les données de commande
     */
    public function up(): void
    {
        Schema::table('transport_tickets', function (Blueprint $table) {
            // Ajout de listing_id si manquant
            if (!Schema::hasColumn('transport_tickets', 'listing_id')) {
                $table->unsignedInteger('listing_id')->default(0)->after('id');
            }
            // Ajout de event_pass_type_id si manquant
            if (!Schema::hasColumn('transport_tickets', 'event_pass_type_id')) {
                $table->unsignedInteger('event_pass_type_id')->default(0)->after('listing_id');
            }
            // Ajout de metadata si manquant
            if (!Schema::hasColumn('transport_tickets', 'metadata')) {
                $table->json('metadata')->nullable()->after('status');
            }
        });

        // ✅ Corriger l'ENUM de dao_proposals pour inclure FAILED_QUORUM
        // MySQL ne permet pas de modifier un ENUM avec Eloquent directement,
        // on utilise une requête SQL brute.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dao_proposals MODIFY COLUMN status ENUM(
            'PENDING', 
            'ACTIVE', 
            'PASSED', 
            'REJECTED', 
            'EXECUTED',
            'FAILED_QUORUM'
        ) NOT NULL DEFAULT 'PENDING'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_tickets', function (Blueprint $table) {
            $table->dropColumn(['listing_id', 'event_pass_type_id', 'metadata']);
        });

        // Remettre l'ancien ENUM sans FAILED_QUORUM
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE dao_proposals MODIFY COLUMN status ENUM(
            'PENDING', 
            'ACTIVE', 
            'PASSED', 
            'REJECTED', 
            'EXECUTED'
        ) NOT NULL DEFAULT 'PENDING'");
        }
    }
};
