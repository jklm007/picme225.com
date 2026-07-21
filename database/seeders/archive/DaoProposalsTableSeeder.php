<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\DaoProposal;
use App\Models\DaoVote;
use App\Models\User;
use Carbon\Carbon;

class DaoProposalsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DaoVote::truncate();
        DaoProposal::truncate();
        Schema::enableForeignKeyConstraints();

        // Ensure we have a user to attribute the proposal to
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'first_name' => 'Admin',
                'last_name' => 'DAO',
                'email' => 'admin@dao.com',
                'password' => bcrypt('password'),
                'mobile' => '0102030405',
                'wallet_balance' => 0,
                'payment_mode' => 'CASH'
            ]);
        }

        $proposals = [
            [
                'blockchain_proposal_id' => '101',
                'user_id' => $user->id,
                'type' => 'PRICE_CHANGE',
                'title' => 'Increase Base Fare by 10%',
                'description' => 'Proposal to increase the base fare for all rides by 10% to account for rising fuel costs. This will ensure better driver compensation.',
                'status' => 'ACTIVE',
                'start_time' => Carbon::now(),
                'end_time' => Carbon::now()->addDays(5),
                'votes_for' => 1500,
                'votes_against' => 200,
                'votes_abstain' => 50,
            ],
            [
                'blockchain_proposal_id' => '102',
                'user_id' => $user->id,
                'type' => 'ROUTE_ADDITION',
                'title' => 'New Express Route: Abidjan - Bassam',
                'description' => 'Launch a new dedicated express route between Abidjan and Grand-Bassam with a fixed price of 2000 FCFA/ECO.',
                'status' => 'ACTIVE',
                'start_time' => Carbon::now()->subDays(1),
                'end_time' => Carbon::now()->addDays(2),
                'votes_for' => 3000,
                'votes_against' => 100,
                'votes_abstain' => 0,
            ],
            [
                'blockchain_proposal_id' => '103',
                'user_id' => $user->id,
                'type' => 'PARAMETER_CHANGE',
                'title' => 'Reduce Syndicate Fee to 2%',
                'description' => 'Current syndicate fee is 5%. This proposal aims to reduce it to 2% to increase driver net earnings.',
                'status' => 'PASSED', // Already finished
                'start_time' => Carbon::now()->subDays(10),
                'end_time' => Carbon::now()->subDays(3),
                'votes_for' => 5000,
                'votes_against' => 500,
                'votes_abstain' => 100,
                'executed' => false,
            ],
            [
                'blockchain_proposal_id' => '104',
                'user_id' => $user->id,
                'type' => 'PARAMETER_CHANGE',
                'title' => 'Fonds de Soutien Créateurs TDR',
                'description' => 'Allouer 5000 ECO de la trésorerie DAO pour financer les premiers concours de créateurs sur Temps de Rose. Cela boostera l\'engagement sur la plateforme.',
                'status' => 'ACTIVE',
                'start_time' => Carbon::now(),
                'end_time' => Carbon::now()->addDays(14),
                'votes_for' => 850,
                'votes_against' => 50,
                'votes_abstain' => 10,
                'executed' => false,
            ]
        ];

        foreach ($proposals as $proposal) {
            DaoProposal::create($proposal);
        }
    }
}
