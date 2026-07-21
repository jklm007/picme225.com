<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\KmHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * LocationSeeder
 *
 * Peuple la catégorie "Location" (type=rental) avec ses types de véhicules.
 * Supporte deux variantes :
 *  - "avec_chauffeur" : véhicule + chauffeur (calcul à l'heure)
 *  - "sans_chauffeur" : location pure (calcul à la journée / forfait)
 *
 * Usage : php artisan db:seed --class=LocationSeeder
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }

        // Récupérer ou créer la catégorie "Location"
        $locationService = Service::firstOrCreate(
            ['name' => 'Location'],
            ['image' => 'rental.jpg']
        );

        // Supprimer les anciens types rental pour repartir proprement
        $oldIds = DB::table('service_service_type')
            ->where('service_id', $locationService->id)
            ->pluck('service_type_id')
            ->toArray();

        if (!empty($oldIds)) {
            DB::table('km_hour_service_type_prices')->whereIn('service_type_id', $oldIds)->delete();
            DB::table('service_service_type')->where('service_id', $locationService->id)->delete();
            ServiceType::whereIn('id', $oldIds)->where('type', 'rental')->delete();
        }

        $vehicles = [
            [
                'name'                  => 'Berline',
                'type'                  => 'rental',
                'provider_name'         => 'Chauffeur Location',
                'image'                 => 'service/taxi_vtc.png',
                'capacity'              => 4,
                'allowed_variants'      => ['avec_chauffeur', 'sans_chauffeur'],
                'fixed'                 => 0,
                'price'                 => 0,
                'minute'                => 0,
                'hour'                  => 5000,     // 5 000 FCFA / heure avec chauffeur
                'distance'              => 1,
                'calculator'            => 'HOUR',
                'rental_amount'         => 5000,
                'commission_percentage' => 15,
                'is_intercommunal'      => 1,
                'is_communal'           => 0,
                'max_distance'          => 0,
                'description'           => 'Berline climatisée à l\'heure. Chauffeur inclus en option.',
                'status'                => 1,
            ],
            [
                'name'                  => 'SUV',
                'type'                  => 'rental',
                'provider_name'         => 'Chauffeur Location',
                'image'                 => 'service/suv.png',
                'capacity'              => 7,
                'allowed_variants'      => ['avec_chauffeur', 'sans_chauffeur'],
                'fixed'                 => 0,
                'price'                 => 0,
                'minute'                => 0,
                'hour'                  => 8000,     // 8 000 FCFA / heure avec chauffeur
                'distance'              => 1,
                'calculator'            => 'HOUR',
                'rental_amount'         => 8000,
                'commission_percentage' => 15,
                'is_intercommunal'      => 1,
                'is_communal'           => 0,
                'max_distance'          => 0,
                'description'           => 'SUV 7 places à l\'heure. Idéal pour les familles.',
                'status'                => 1,
            ],
            [
                'name'                  => 'Minibus',
                'type'                  => 'rental',
                'provider_name'         => 'Chauffeur Location',
                'image'                 => 'service/inter-communal.png',
                'capacity'              => 14,
                'allowed_variants'      => ['avec_chauffeur', 'sans_chauffeur'],
                'fixed'                 => 0,
                'price'                 => 0,
                'minute'                => 0,
                'hour'                  => 15000,    // 15 000 FCFA / heure avec chauffeur
                'distance'              => 1,
                'calculator'            => 'HOUR',
                'rental_amount'         => 15000,
                'commission_percentage' => 15,
                'is_intercommunal'      => 1,
                'is_communal'           => 0,
                'max_distance'          => 0,
                'description'           => 'Minibus 14 places. Parfait pour les groupes et sorties.',
                'status'                => 1,
            ],
        ];

        foreach ($vehicles as $data) {
            $st = ServiceType::create($data);

            // Liaison catégorie ↔ type
            DB::table('service_service_type')->insert(array_merge(
                $this->pivotBase($data),
                [
                    'service_id'     => $locationService->id,
                    'service_type_id'=> $st->id,
                    'rental_amount'  => $data['rental_amount'] ?? 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            ));

            // Forfaits horaires (km_hour_service_type_prices)
            $packages = KmHour::all();
            foreach ($packages as $pkg) {
                $baseHourPrice = $data['rental_amount'];
                $totalPrice    = $baseHourPrice * $pkg->hour;

                // Dégressivité selon la durée
                if ($pkg->hour >= 4)  $totalPrice *= 0.90;  // -10%
                if ($pkg->hour >= 12) $totalPrice *= 0.85;  // -15%
                if ($pkg->hour >= 24) $totalPrice *= 0.75;  // -25%

                DB::table('km_hour_service_type_prices')->insert([
                    'km_hour_id'      => $pkg->id,
                    'service_type_id' => $st->id,
                    'price'           => round($totalPrice),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            $this->command->info("  ✓ Location : {$data['name']} créé (ID: {$st->id})");
        }

        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }
        $this->command->info('LocationSeeder terminé.');
    }

    private function pivotBase(array $data): array
    {
        return [
            'name'         => $data['name'],
            'provider_name'=> $data['provider_name'] ?? null,
            'image'        => $data['image'] ?? null,
            'capacity'     => $data['capacity'] ?? 0,
            'fixed'        => $data['fixed'] ?? 0,
            'price'        => $data['price'] ?? 0,
            'minute'       => $data['minute'] ?? 0,
            'hour'         => $data['hour'] ?? null,
            'distance'     => $data['distance'] ?? 1,
            'calculator'   => $data['calculator'] ?? 'DISTANCE',
            'description'  => $data['description'] ?? '',
            'status'       => $data['status'] ?? 1,
        ];
    }
}
