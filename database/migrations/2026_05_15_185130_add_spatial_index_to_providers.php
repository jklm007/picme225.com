<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration: Optimisation Géographique pour le Moteur de Dispatch IA
 *
 * Cette migration ajoute des index MySQL qui permettent d'accélérer
 * radicalement les recherches géographiques de chauffeurs.
 *
 * Stratégie (sans Redis, 100% MySQL gratuit) :
 * 1. Index sur (latitude, longitude) pour les requêtes Bounding Box.
 * 2. Colonnes de performance pour le ScoreService.
 *
 * SÉCURITÉ : La migration est réversible (down() restaure l'état initial).
 * COMPATIBILITÉ : Compatible MySQL 5.7+ et MariaDB 10.2+.
 */
class AddSpatialIndexToProviders extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {

            // --- OPTIMISATION 1 : Index composite lat/lng ---
            // Utilisé par la Bounding Box dans GeoService pour réduire le scan SQL
            // de ~10 000 lignes à ~20-50 lignes avant le calcul Haversine précis.
            if (!$this->indexExists('providers', 'idx_provider_location')) {
                $table->index(['latitude', 'longitude'], 'idx_provider_location');
            }

            // --- OPTIMISATION 2 : Index sur status pour le filtre "approved" ---
            if (!$this->indexExists('providers', 'idx_provider_status')) {
                $table->index('status', 'idx_provider_status');
            }

            // --- COLONNES SCORE SERVICE ---
            // Historique des comportements pour le calcul de score IA

            // Taux d'acceptation (0-100). Mis à jour après chaque course proposée.
            if (!Schema::hasColumn('providers', 'acceptance_rate')) {
                $table->tinyInteger('acceptance_rate')->default(100)->after('rating');
            }

            // Total de courses proposées (pour calculer le taux d'acceptation)
            if (!Schema::hasColumn('providers', 'total_offered_trips')) {
                $table->integer('total_offered_trips')->default(0)->after('acceptance_rate');
            }

            // Total de courses acceptées
            if (!Schema::hasColumn('providers', 'total_accepted_trips')) {
                $table->integer('total_accepted_trips')->default(0)->after('total_offered_trips');
            }

            // Zone géographique favorite (Geohash 5 chars = ~4km²)
            // Stocke la zone où le chauffeur passe le plus de temps
            if (!Schema::hasColumn('providers', 'home_geohash')) {
                $table->string('home_geohash', 10)->nullable()->after('smart_dest_address');
            }

            // Score de qualité calculé par le ScoreService (0-100)
            // Mis à jour toutes les 24h. Visible en admin.
            if (!Schema::hasColumn('providers', 'dispatch_score')) {
                $table->tinyInteger('dispatch_score')->default(50)->after('trust_score');
            }

            // Timestamp du dernier calcul de score (pour éviter les recalculs inutiles)
            if (!Schema::hasColumn('providers', 'score_updated_at')) {
                $table->timestamp('score_updated_at')->nullable()->after('dispatch_score');
            }
        });
    }

    /**
     * Reverse the migrations (rollback propre).
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            // Suppression des index
            if ($this->indexExists('providers', 'idx_provider_location')) {
                $table->dropIndex('idx_provider_location');
            }
            if ($this->indexExists('providers', 'idx_provider_status')) {
                $table->dropIndex('idx_provider_status');
            }

            // Suppression des colonnes ajoutées
            $columnsToDrop = [
                'acceptance_rate', 'total_offered_trips', 'total_accepted_trips',
                'home_geohash', 'dispatch_score', 'score_updated_at'
            ];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('providers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Helper: vérifie si un index existe déjà sur la table.
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $driver = \DB::getDriverName();
            if ($driver === 'mysql') {
                $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
                return count($indexes) > 0;
            } elseif ($driver === 'pgsql') {
                $indexes = \DB::select("SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $index]);
                return count($indexes) > 0;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
