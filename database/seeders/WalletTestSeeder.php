<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WalletTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userId = 1; // KOUAKOU ANTOINE
        
        // Nettoyer l'historique de test
        DB::table('wallet_passbooks')->where('user_id', $userId)->delete();

        $transactions = [
            [
                'user_id' => $userId,
                'amount' => 5000,
                'status' => 'CREDITED',
                'via' => 'RECHARGE WAVE',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $userId,
                'amount' => 1200,
                'status' => 'DEBITED',
                'via' => 'TRIP',
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            [
                'user_id' => $userId,
                'amount' => 2500,
                'status' => 'CREDITED',
                'via' => 'REFUND',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $userId,
                'amount' => 800,
                'status' => 'DEBITED',
                'via' => 'AD_PROMOTION',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $userId,
                'amount' => 10000,
                'status' => 'CREDITED',
                'via' => 'RECHARGE ORANGE',
                'created_at' => Carbon::now()->subHours(5),
                'updated_at' => Carbon::now()->subHours(5),
            ],
            [
                'user_id' => $userId,
                'amount' => 3500,
                'status' => 'DEBITED',
                'via' => 'MARKETPLACE_PURCHASE',
                'created_at' => Carbon::now()->subMinutes(30),
                'updated_at' => Carbon::now()->subMinutes(30),
            ],
        ];

        DB::table('wallet_passbooks')->insert($transactions);
        
        // Mettre à jour le solde de l'utilisateur pour qu'il soit cohérent
        $balance = DB::table('wallet_passbooks')->where('user_id', $userId)
            ->selectRaw("SUM(CASE WHEN status = 'CREDITED' THEN amount ELSE -amount END) as balance")
            ->value('balance');
            
        DB::table('users')->where('id', $userId)->update(['wallet_balance' => $balance]);

        $this->command->info("Historique de portefeuille créé pour l'utilisateur 1 ! Nouveau solde: $balance");
    }
}
