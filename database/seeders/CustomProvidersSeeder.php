<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomProvidersSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }

        // Nettoyage des chauffeurs de test précédents (optionnel)
        DB::table('providers')->where('email', 'like', 'driver.%@picme.com')->delete();

        $drivers = [
            [
                'first_name' => 'Jean',
                'last_name' => 'VTC',
                'email' => 'driver.vtc@picme.com',
                'service_name' => 'Taxi Vtc',
                'mobile' => '0101010101',
            ],
            [
                'first_name' => 'Moussa',
                'last_name' => 'Compteur',
                'email' => 'driver.compteur@picme.com',
                'service_name' => 'Taxi Compteur',
                'mobile' => '0202020202',
            ],
            [
                'first_name' => 'Koffi',
                'last_name' => 'Bus',
                'email' => 'driver.bus@picme.com',
                'service_name' => 'Inter-communal',
                'mobile' => '0303030303',
            ],
            [
                'first_name' => 'Alain',
                'last_name' => 'SUV',
                'email' => 'driver.suv@picme.com',
                'service_name' => 'SUV',
                'mobile' => '0404040404',
            ],
            [
                'first_name' => 'Bakary',
                'last_name' => 'Moto',
                'email' => 'driver.moto@picme.com',
                'service_name' => 'Moto',
                'mobile' => '0505050505',
            ],
            [
                'first_name' => 'Sékou',
                'last_name' => 'Cargo',
                'email' => 'driver.cargo@picme.com',
                'service_name' => 'Cargo',
                'mobile' => '0606060606',
            ],
            [
                'first_name' => 'Gérard',
                'last_name' => 'Rent Berline',
                'email' => 'driver.rent.berline@picme.com',
                'service_name' => 'Berline', // Dans la catégorie Location
                'mobile' => '0707070707',
            ],
            [
                'first_name' => 'Hervé',
                'last_name' => 'Rent SUV',
                'email' => 'driver.rent.suv@picme.com',
                'service_name' => 'SUV', // Dans la catégorie Location
                'mobile' => '0808080808',
            ],
            [
                'first_name' => 'Dr. Kouassi',
                'last_name' => 'Ambulance',
                'email' => 'driver.ambulance@picme.com',
                'service_name' => 'Ambulance',
                'mobile' => '0909090909',
            ],
        ];

        foreach ($drivers as $d) {
            // 1. Créer le Provider
            $providerId = DB::table('providers')->insertGetId([
                'first_name' => $d['first_name'],
                'last_name' => $d['last_name'],
                'email' => $d['email'],
                'password' => bcrypt('123456'),
                'mobile' => $d['mobile'],
                'login_by' => 'manual',
                'status' => 'approved', // Approuvé direct
                'available' => 1,
                'commune' => 'Cocody',
                'latitude' => 5.3484,  // Abidjan
                'longitude' => -4.0305,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 2. Trouver le service_type_id correspondant au nom
            $query = DB::table('service_types')->where('name', $d['service_name']);
            if (strpos($d['email'], 'rent') !== false) {
                $query->where('type', 'rental');
            } else {
                $query->where('type', '!=', 'rental');
            }
            $serviceType = $query->first();

            if ($serviceType) {
                // 3. Assigner le service au chauffeur
                DB::table('provider_services')->insert([
                    'provider_id' => $providerId,
                    'service_type_id' => $serviceType->id,
                    'status' => 'active',
                    'service_number' => 'ABC-' . rand(100, 999),
                    'service_model' => 'Modèle ' . $d['service_name'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }
    }
}
