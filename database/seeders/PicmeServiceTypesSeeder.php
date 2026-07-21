<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\KmHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ============================================================
 * PICME PRO - SEEDER FONDATION (CATÉGORIES & FORFAITS)
 * ============================================================
 * Ce seeder prépare uniquement la structure de base :
 * 1. Les catégories principales (Services)
 * 2. Les forfaits de location universels (Km/Heures)
 * 
 * Les types de véhicules (ServiceTypes) doivent être créés
 * manuellement par l'administrateur via le panel.
 */
class PicmeServiceTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Driver compatible truncate
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE services CASCADE;');
        } else {
            DB::statement('TRUNCATE TABLE services;');
        }


        // ─────────────────────────────────────────────────────
        // 2. CRÉATION DES CATÉGORIES PRINCIPALES (SERVICES)
        // ─────────────────────────────────────────────────────
        $categories = [
            'Taxi'       => 'service/standard.jpg',
            'Livraison'  => 'service/delivery_main.png', 
            'Location'   => 'service/rental.jpg',      
            'Voyage'     => 'service/outstation.jpg',  
            'Urgence'    => 'service/ambulance.jpg',   
            'Partage'    => 'service/shared_ride.jpg', 
        ];

        foreach ($categories as $catName => $catImage) {
            Service::create([
                'name' => $catName, 
                'image' => $catImage
            ]);
        }

        // ─────────────────────────────────────────────────────
        // 3. CRÉATION DES FORFAITS DE LOCATION (KM-HOUR)
        // ─────────────────────────────────────────────────────
        $packagesData = [
            ['hour' => 1, 'kilometer' => 20],
            ['hour' => 2, 'kilometer' => 30],
            ['hour' => 4, 'kilometer' => 45],
            ['hour' => 8, 'kilometer' => 80],
            ['hour' => 12, 'kilometer' => 150],
            ['hour' => 24, 'kilometer' => 300],
        ];

        foreach ($packagesData as $pkg) {
            KmHour::create($pkg);
        }



        $this->command->info('[OK] Fondation Picme Pro installée avec succès.');
        $this->command->info('     - ' . count($categories) . ' Catégories créées.');
        $this->command->info('     - ' . count($packagesData) . ' Forfaits Km/Heure créés.');
        $this->command->info('     Note: Créez vos véhicules via le panel Admin maintenant.');
    }
}
