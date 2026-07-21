<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpansionServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // VIP / Premium Service
        DB::table('service_types')->updateOrInsert(
            ['name' => 'Premium VIP'],
            [
                'provider_name' => 'Elite Driver',
                'fixed' => 50, // Higher fixed fare
                'price' => 20, // Higher price per unit
                'status' => 1,
                'minute' => 2,
                'distance' => '1',
                'calculator' => 'DISTANCE',
                'image' => asset('asset/img/cars/vip.png'),
                'capacity' => 4,
                'description' => 'Service de luxe avec chauffeurs certifiés et véhicules haut de gamme.',
                'commission_percentage' => 15,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // School Transport
        DB::table('service_types')->updateOrInsert(
            ['name' => 'Transport Scolaire'],
            [
                'provider_name' => 'School Bus',
                'fixed' => 100, // Monthly or per-trip base
                'price' => 5, // Lower distance price
                'status' => 1,
                'minute' => 0,
                'distance' => '1',
                'calculator' => 'DISTANCE',
                'image' => asset('asset/img/cars/school_bus.png'),
                'capacity' => 15,
                'description' => 'Transport sécurisé pour les élèves. Accompagnement et suivi en temps réel.',
                'commission_percentage' => 10,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
