<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;

class ServicesTableSeeder extends Seeder
{
    public function run()
    {
        // Désactiver les vérifications des clés étrangères
        if (\DB::getDriverName() === 'mysql') { \if (\DB::getDriverName() === 'mysql') { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); }

        // Tronquer la table
        Service::truncate();

        // Réactiver les vérifications des clés étrangères
        if (\DB::getDriverName() === 'mysql') { \if (\DB::getDriverName() === 'mysql') { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } } elseif (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); }

        // Insérer les données
        $services = [
            ['name' => 'Taxi', 'image' => 'standard.jpg'],
            ['name' => 'Location', 'image' => 'rental.jpg'],
            ['name' => 'Voyage', 'image' => 'outstation.jpg'],
            ['name' => 'Urgence', 'image' => 'ambulance.jpg'],
            ['name' => 'Livraison ', 'image' => 'shared_ride.jpg'],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}

