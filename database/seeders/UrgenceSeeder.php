<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * UrgenceSeeder
 *
 * Peuple la catégorie "Urgence" (type=urgence) avec ses types de véhicules.
 * Supporte deux variantes :
 *  - "ambulance"  : transport médical d'urgence (autocomplétion filtrée hôpitaux/cliniques)
 *  - "depannage"  : dépannage automobile (autocomplétion libre)
 *
 * Usage : php artisan db:seed --class=UrgenceSeeder
 */
class UrgenceSeeder extends Seeder
{
    public function run(): void
    {
        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'replica';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=0;'); }

        // Récupérer ou créer la catégorie "Urgence"
        $urgenceService = Service::firstOrCreate(
            ['name' => 'Urgence'],
            ['image' => 'service/ambulance.jpg']
        );

        // Supprimer les anciens types urgence pour repartir proprement
        $oldIds = DB::table('service_service_type')
            ->where('service_id', $urgenceService->id)
            ->pluck('service_type_id')
            ->toArray();

        if (!empty($oldIds)) {
            DB::table('service_service_type')->where('service_id', $urgenceService->id)->delete();
            ServiceType::whereIn('id', $oldIds)->where('type', 'urgence')->delete();
        }

        $vehicles = [
            // ─────────────────────────────────────────────────────────────────
            // AMBULANCE : transport médical
            // L'autocomplétion "Où allons-nous ?" sera filtrée sur hôpitaux/cliniques
            // ─────────────────────────────────────────────────────────────────
            [
                'name'                  => 'Ambulance',
                'type'                  => 'urgence',
                'provider_name'         => 'Ambulancier',
                'image'                 => 'service/ambulance.png',
                'capacity'              => 2,
                'allowed_variants'      => ['ambulance'],
                'fixed'                 => 5000,    // Prise en charge fixe
                'price'                 => 500,     // FCFA / km
                'minute'                => 0,
                'hour'                  => 0,
                'distance'              => 1,
                'calculator'            => 'DISTANCE',
                'commission_percentage' => 0,       // Pas de commission sur les urgences
                'ambulance'             => 1,
                'is_intercommunal'      => 1,
                'is_communal'           => 0,
                'max_distance'          => 200,
                'description'           => 'Ambulance médicalisée d\'urgence. Destination : hôpitaux et cliniques.',
                'status'                => 1,
            ],
            // ─────────────────────────────────────────────────────────────────
            // DÉPANNEUSE : remorquage / dépannage auto
            // L'autocomplétion "Où allons-nous ?" est libre (garage, domicile, etc.)
            // ─────────────────────────────────────────────────────────────────
            [
                'name'                  => 'Dépanneuse',
                'type'                  => 'urgence',
                'provider_name'         => 'Dépanneur',
                'image'                 => 'service/cargo.png',
                'capacity'              => 1,
                'allowed_variants'      => ['depannage'],
                'fixed'                 => 3000,    // Prise en charge fixe
                'price'                 => 400,     // FCFA / km
                'minute'                => 0,
                'hour'                  => 0,
                'distance'              => 1,
                'calculator'            => 'DISTANCE',
                'commission_percentage' => 15,
                'ambulance'             => 0,
                'is_intercommunal'      => 1,
                'is_communal'           => 0,
                'max_distance'          => 100,
                'description'           => 'Remorquage et dépannage automobile. Nous venons à vous.',
                'status'                => 1,
            ],
            // ─────────────────────────────────────────────────────────────────
            // AMBULANCE PRIVÉE : plus confortable, sur abonnement / vip
            // ─────────────────────────────────────────────────────────────────
            [
                'name'                  => 'Ambulance Privée',
                'type'                  => 'urgence',
                'provider_name'         => 'Ambulancier VIP',
                'image'                 => 'service/ambulance.png',
                'capacity'              => 4,
                'allowed_variants'      => ['ambulance'],
                'fixed'                 => 10000,
                'price'                 => 800,
                'minute'                => 0,
                'hour'                  => 0,
                'distance'              => 1,
                'calculator'            => 'DISTANCE',
                'commission_percentage' => 0,
                'ambulance'             => 1,
                'is_intercommunal'      => 1,
                'is_communal'           => 0,
                'max_distance'          => 300,
                'description'           => 'Ambulance privée médicalisée avec équipement avancé.',
                'status'                => 1,
            ],
        ];

        foreach ($vehicles as $data) {
            $st = ServiceType::create($data);

            DB::table('service_service_type')->insert(array_merge(
                $this->pivotBase($data),
                [
                    'service_id'      => $urgenceService->id,
                    'service_type_id' => $st->id,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            ));

            $this->command->info("  ✓ Urgence : {$data['name']} créé (ID: {$st->id})");
        }

        if (\DB::getDriverName() === 'pgsql') { \DB::statement("SET session_replication_role = 'origin';"); } else { \DB::statement('SET FOREIGN_KEY_CHECKS=1;'); }
        $this->command->info('UrgenceSeeder terminé.');
    }

    private function pivotBase(array $data): array
    {
        return [
            'name'          => $data['name'],
            'provider_name' => $data['provider_name'] ?? null,
            'image'         => $data['image'] ?? null,
            'capacity'      => $data['capacity'] ?? 0,
            'fixed'         => $data['fixed'] ?? 0,
            'price'         => $data['price'] ?? 0,
            'minute'        => $data['minute'] ?? 0,
            'hour'          => $data['hour'] ?? null,
            'distance'      => $data['distance'] ?? 1,
            'calculator'    => $data['calculator'] ?? 'DISTANCE',
            'ambulance'     => $data['ambulance'] ?? 0,
            'description'   => $data['description'] ?? '',
            'status'        => $data['status'] ?? 1,
        ];
    }
}
