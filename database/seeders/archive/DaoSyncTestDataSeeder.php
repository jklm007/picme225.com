<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DaoSyncTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On crée quelques paiements fictifs avec des frais DAO non synchronisés
        for ($i = 0; $i < 5; $i++) {
            \App\Models\UserRequestPayment::create([
                'request_id' => 1, // Supposons qu'une requête ID 1 existe ou peu importe pour le test
                'payment_mode' => 'CASH',
                'fixed' => 1000,
                'distance' => 500,
                'commision' => 200,
                'total' => 1700,
                'payable' => 1700,
                'provider_pay' => 1500,
                'dao_treasury_fee' => rand(50, 150),
                'is_synced_to_chain' => false
            ]);
        }
    }
}
