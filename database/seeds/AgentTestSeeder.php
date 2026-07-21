<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StationAgent;
use App\Models\PdpStop;
use App\Models\InterurbanCompany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AgentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            // 1. Créer ou récupérer une gare de test
            $stop = PdpStop::firstOrCreate(
                ['name' => 'Gare Test Agent'],
                [
                    'latitude' => 5.3364,
                    'longitude' => -4.0266,
                    'city' => 'Abidjan',
                    'status' => 'ACTIVE'
                ]
            );

            // 2. Créer l'utilisateur pour l'agent
            $user = User::updateOrCreate(
                ['email' => 'agent@test.com'],
                [
                    'first_name' => 'Agent',
                    'last_name' => 'Test',
                    'mobile' => '0000000001',
                    'password' => Hash::make('password'),
                    'payment_mode' => 'CASH',
                    'wallet_balance' => 0
                ]
            );

            // 3. Assigner le rôle Agent
            $agent = StationAgent::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'pdp_stop_id' => $stop->id,
                    'commission_per_passenger' => 100, // ou 15% calculé dynamiquement
                    'wallet_balance' => 50000, // Rechargé pour éviter le plafond négatif au premier test
                    'status' => 'ACTIVE'
                ]
            );

            DB::commit();

            echo "Test Agent créé avec succès !\n";
            echo "Email : agent@test.com\n";
            echo "Mot de passe : password\n";
            echo "Wallet Agent : 50 000 FCFA\n";

        } catch (\Exception $e) {
            DB::rollBack();
            echo "Erreur lors de la création de l'agent: " . $e->getMessage() . "\n";
        }
    }
}
