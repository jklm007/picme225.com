<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * UserSubscriptionPlanSeeder
 *
 * Seeds user-facing subscription plans (Work Pass, School Pass, Custom).
 * Provider plans are seeded separately by SubscriptionPlanSeeder.
 *
 * Run: php artisan db:seed --class=UserSubscriptionPlanSeeder
 */
class UserSubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $plans = [
            // ── WORK PASS (Abonnement Travail) ─────────────────────────────
            [
                'name'                    => 'WORK PASS — Mensuel',
                'description'             => 'Pass mensuel pour les travailleurs. Accès prioritaire aux courses domicile-travail aux heures de pointe. Commission réduite à 10%.',
                'badge_url'               => null,
                'price'                   => 5000.00,
                'period'                  => 'MONTHLY',
                'commission_type'         => 'percentage',
                'commission_value'        => 10.00,
                'fixed_fee'               => 0.00,
                'priority'                => 300,
                'priority_weight'         => 30,
                'insurance_included'      => true,
                'staking_bonus_percentage'=> 0.00,
                'show_on_marketplace'     => true,
                'max_categories'          => 3,
                'status'                  => 'active',
                'service_id'              => null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],

            // ── SCHOOL PASS (Abonnement Scolaire) ─────────────────────────
            [
                'name'                    => 'SCHOOL PASS — Mensuel',
                'description'             => 'Pass mensuel étudiant. Trajets scolaires prioritaires. Tarif réduit spécial élèves et étudiants. Commission à 8%.',
                'badge_url'               => null,
                'price'                   => 3000.00,
                'period'                  => 'MONTHLY',
                'commission_type'         => 'percentage',
                'commission_value'        => 8.00,
                'fixed_fee'               => 0.00,
                'priority'                => 250,
                'priority_weight'         => 25,
                'insurance_included'      => true,
                'staking_bonus_percentage'=> 0.00,
                'show_on_marketplace'     => true,
                'max_categories'          => 2,
                'status'                  => 'active',
                'service_id'              => null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],

            // ── WORK PASS HEBDO ───────────────────────────────────────────
            [
                'name'                    => 'WORK PASS — Hebdomadaire',
                'description'             => 'Pass semaine pour les travailleurs. Idéal pour tester le service. Commission réduite à 12%.',
                'badge_url'               => null,
                'price'                   => 1500.00,
                'period'                  => 'WEEKLY',
                'commission_type'         => 'percentage',
                'commission_value'        => 12.00,
                'fixed_fee'               => 0.00,
                'priority'                => 200,
                'priority_weight'         => 20,
                'insurance_included'      => true,
                'staking_bonus_percentage'=> 0.00,
                'show_on_marketplace'     => true,
                'max_categories'          => 2,
                'status'                  => 'active',
                'service_id'              => null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],

            // ── PREMIUM PASS (Accès toutes catégories) ────────────────────
            [
                'name'                    => 'PREMIUM PASS — Mensuel',
                'description'             => 'Accès premium illimité à tous les services PicMe : Taxi, Livraison, Location. Commission ultra-réduite à 5%. Badge Premium.',
                'badge_url'               => null,
                'price'                   => 10000.00,
                'period'                  => 'MONTHLY',
                'commission_type'         => 'percentage',
                'commission_value'        => 5.00,
                'fixed_fee'               => 0.00,
                'priority'                => 500,
                'priority_weight'         => 50,
                'insurance_included'      => true,
                'staking_bonus_percentage'=> 2.50,
                'show_on_marketplace'     => true,
                'max_categories'          => 10,
                'status'                  => 'active',
                'service_id'              => null,
                'created_at'              => $now,
                'updated_at'              => $now,
            ],
        ];

        foreach ($plans as $plan) {
            // Upsert by name to prevent duplicates on re-run
            DB::table('subscription_plans')
                ->updateOrInsert(
                    ['name' => $plan['name']],
                    $plan
                );
        }

        $this->command->info('UserSubscriptionPlanSeeder: ' . count($plans) . ' plans seeded.');
    }
}
