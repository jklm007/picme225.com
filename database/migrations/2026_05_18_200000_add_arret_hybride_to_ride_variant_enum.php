<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajouter les nouvelles variantes de course au système.
 *
 * Avant : enum('prive','dynamique','arret')
 * Après : enum('prive','dynamique','arret','partage','arret_pdp','arret_hybride')
 *
 * Variantes :
 *  - prive        : Course exclusive porte-à-porte (Taxi/Standard)
 *  - partage      : Covoiturage dynamique (Taxi en mode partage, abonnement requis)
 *  - arret_pdp    : Navette de gare fixe à gare fixe (Taxi Inter-communal / lignes PDP)
 *  - arret_hybride: NOUVEAU - Prise en charge sur arrêt fixe, destination LIBRE
 *                   (Inter-communal / Share-Ride uniquement)
 *                   Prix = Somme des segments parcourus + km restants × price_per_km
 *  - dynamique    : Alias legacy de 'partage' (gardé pour compatibilité)
 *  - arret        : Alias legacy de 'arret_pdp' (gardé pour compatibilité)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Étendre l'enum ride_variant pour inclure toutes les nouvelles variantes
        if (DB::connection()->getDriverName() === 'mysql') {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("
            ALTER TABLE user_requests
            MODIFY COLUMN ride_variant
            ENUM('prive','dynamique','arret','partage','arret_pdp','arret_hybride')
            NOT NULL DEFAULT 'prive'
        ");
            }
        }

        // 2. Reconfigurer les allowed_variants du service type Inter-communal (ID:3)
        //    Il ne doit PAS avoir 'prive' (réservé au Taxi). Sa variante 'partage'
        //    est remplacée par 'arret_hybride' (dernier kilomètre).
        DB::table('service_types')
            ->where('id', 3)
            ->where('name', 'Inter-communal')
            ->update([
                'allowed_variants' => json_encode(['arret_pdp', 'arret_hybride']),
                'updated_at' => now(),
            ]);

        // 3. S'assurer que les Taxi (ID:1, 2, 4) gardent leurs 3 variantes correctes
        //    prive + partage + arret_pdp
        DB::table('service_types')
            ->whereIn('id', [1, 2, 4])
            ->update([
                'allowed_variants' => json_encode(['prive', 'partage', 'arret_pdp']),
                'updated_at' => now(),
            ]);

        \Illuminate\Support\Facades\Log::info('[Migration arret_hybride] ride_variant enum étendu + allowed_variants mis à jour.');
    }

    public function down(): void
    {
        // Revenir à l'ancien enum (garder la rétrocompatibilité)
        if (DB::connection()->getDriverName() === 'mysql') {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("
            ALTER TABLE user_requests
            MODIFY COLUMN ride_variant
            ENUM('prive','dynamique','arret')
            NOT NULL DEFAULT 'prive'
        ");
            }
        }

        // Restaurer l'Inter-communal à son ancien état
        DB::table('service_types')
            ->where('id', 3)
            ->update([
                'allowed_variants' => json_encode(['partage', 'arret_pdp']),
                'updated_at' => now(),
            ]);

        // Restaurer les Taxi
        DB::table('service_types')
            ->whereIn('id', [1, 2, 4])
            ->update([
                'allowed_variants' => json_encode(['prive', 'partage', 'arret_pdp']),
                'updated_at' => now(),
            ]);
    }
};
