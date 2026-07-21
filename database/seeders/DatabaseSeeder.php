<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks (cross-driver compatible)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET session_replication_role = 'replica';");
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $this->call([
            // --- 1. CONFIGURATION CŒUR (Vital) ---
            AdminsTableSeeder::class,    // Comptes administrateurs
            SettingsTableSeeder::class,  // Paramètres app
            DocumentsTableSeeder::class, // Documents requis

            // --- 2. MODULES FONCTIONNELS (PicMe) ---
            PicmeServiceTypesSeeder::class, // Taxi, Location, Voyage, Urgence, Livraison, Partage
            CustomServiceTypesSeeder::class, // Overrides with custom vehicles
            CustomProvidersSeeder::class,    // New test drivers
            SocialMarketplaceSeeder::class, // Social & Marketplace
            PrivateAdSeeder::class,         // Publicités privées
            MarketplacePlansSeeder::class,  // Plans Marketplace vendeurs (Starter/Pro/Business)

            // --- 3. TRANSPORT INTERURBAIN (PDP) ---
            PdpStopsSeeder::class,
            PdpRoutesSeeder::class,

            // --- 4. DONNÉES DE DÉMO & TESTS ---
            DemoSeeder::class,
            AccountsSeeder::class,
            FleetSeeder::class,
            DispatcherSeeder::class,
        ]);

        // Re-enable foreign key checks
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET session_replication_role = 'origin';");
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
