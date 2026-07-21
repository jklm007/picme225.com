<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Antigravity: Ajoute la propriété zone_coverage (COMMUNAL | INTERCOMMUNAL | TOUTE_ZONE)
 * sur la table service_types pour le moteur de filtrage intelligent par trajet.
 *
 * Règles de population automatique depuis les champs booléens existants :
 *  - COMMUNAL      : is_communal = 1
 *  - INTERCOMMUNAL : is_communal = 0 ET is_intercommunal = 1
 *  - TOUTE_ZONE    : is_communal = 0 ET is_intercommunal = 1 ET is_interregional = 1
 *                    (services universels — VTC, Voyage longue-distance)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'zone_coverage')) {
                $table->enum('zone_coverage', ['COMMUNAL', 'INTERCOMMUNAL', 'TOUTE_ZONE'])
                      ->default('COMMUNAL')
                      ->after('is_interregional')
                      ->comment('Couverture géographique du service pour le filtrage intelligent par trajet');
            }
        });

        // Peuplement automatique depuis les champs booléens existants
        // Règle 1 — Services universels (VTC, Voyage inter-régional)
        DB::table('service_types')
            ->where('is_communal', false)
            ->where('is_intercommunal', true)
            ->where('is_interregional', true)
            ->update(['zone_coverage' => 'TOUTE_ZONE']);

        // Règle 2 — Services intercommunaux seulement
        DB::table('service_types')
            ->where('is_communal', false)
            ->where('is_intercommunal', true)
            ->where(function($query) {
                $query->where('is_interregional', false)
                      ->orWhereNull('is_interregional');
            })
            ->update(['zone_coverage' => 'INTERCOMMUNAL']);

        // Règle 3 — Services communaux (valeur par défaut déjà COMMUNAL,
        // mais on force explicitement pour éviter toute ambiguïté)
        DB::table('service_types')
            ->where('is_communal', true)
            ->update(['zone_coverage' => 'COMMUNAL']);
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (Schema::hasColumn('service_types', 'zone_coverage')) {
                $table->dropColumn('zone_coverage');
            }
        });
    }
};
