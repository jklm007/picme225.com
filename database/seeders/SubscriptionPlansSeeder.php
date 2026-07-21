<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Nettoyage
        DB::table('subscription_plans')->delete();

        // ─────────────────────────────────────────────────────
        // 1. LIVRAISON (MOTO) - ID 2
        // ─────────────────────────────────────────────────────
        
        // Plan ECO - HEBDO
        SubscriptionPlan::create([
            'service_id' => 2,
            'name' => 'LIVREUR ECO (Hebdo)',
            'description' => 'Idéal pour tester la plateforme. Commission réduite.',
            'price' => 3000,
            'period' => 'WEEKLY',
            'commission_type' => 'percentage',
            'commission_value' => 12.00,
            'priority' => 200,
            'priority_weight' => 20,
            'max_categories' => 1,
            'status' => 'active'
        ]);

        // Plan PRO - MENSUEL
        SubscriptionPlan::create([
            'service_id' => 2,
            'name' => 'LIVREUR PRO (Mensuel)',
            'description' => 'Le meilleur rapport qualité/prix pour les livreurs quotidiens.',
            'price' => 12000,
            'period' => 'MONTHLY',
            'commission_type' => 'percentage',
            'commission_value' => 7.00,
            'priority' => 500,
            'priority_weight' => 50,
            'max_categories' => 2,
            'insurance_included' => true,
            'status' => 'active'
        ]);

        // Plan GOLD - MENSUEL (0% Commission)
        SubscriptionPlan::create([
            'service_id' => 2,
            'name' => 'LIVREUR GOLD (Zéro %)',
            'description' => 'Zéro commission ! Vous ne payez que 100 CFA par course livrée.',
            'price' => 25000,
            'period' => 'MONTHLY',
            'commission_type' => 'fixed',
            'commission_value' => 0.00,
            'fixed_fee' => 100.00,
            'priority' => 1000,
            'priority_weight' => 100,
            'max_categories' => 3,
            'insurance_included' => true,
            'staking_bonus_percentage' => 2.0,
            'status' => 'active'
        ]);

        // ─────────────────────────────────────────────────────
        // 2. TAXI (STANDARD) - ID 1
        // ─────────────────────────────────────────────────────

        // Plan ECO - HEBDO
        SubscriptionPlan::create([
            'service_id' => 1,
            'name' => 'TAXI ECO (Hebdo)',
            'description' => 'Le "loyer" hebdomadaire pour une commission à 15%.',
            'price' => 5000,
            'period' => 'WEEKLY',
            'commission_type' => 'percentage',
            'commission_value' => 15.00,
            'priority' => 300,
            'priority_weight' => 30,
            'max_categories' => 1,
            'status' => 'active'
        ]);

        // Plan PRO - MENSUEL
        SubscriptionPlan::create([
            'service_id' => 1,
            'name' => 'TAXI PRO (Mensuel)',
            'description' => 'Passez pro avec seulement 8% de commission.',
            'price' => 20000,
            'period' => 'MONTHLY',
            'commission_type' => 'percentage',
            'commission_value' => 8.00,
            'priority' => 700,
            'priority_weight' => 70,
            'max_categories' => 2,
            'insurance_included' => true,
            'status' => 'active'
        ]);

        // Plan GOLD - MENSUEL (Elite)
        SubscriptionPlan::create([
            'service_id' => 1,
            'name' => 'TAXI GOLD (Souverain)',
            'description' => 'Frais fixe de 200 CFA par course. 0% de commission variable.',
            'price' => 40000,
            'period' => 'MONTHLY',
            'commission_type' => 'fixed',
            'commission_value' => 0.00,
            'fixed_fee' => 200.00,
            'priority' => 2000,
            'priority_weight' => 150,
            'max_categories' => 5,
            'insurance_included' => true,
            'staking_bonus_percentage' => 5.0,
            'status' => 'active'
        ]);

        // ─────────────────────────────────────────────────────
        // 3. VOYAGE (PDP / INTERURBAIN) - ID 4 → Commission pure, pas d'abonnement
        // ─────────────────────────────────────────────────────
        // La commission est définie au niveau du service_type (commission_percentage).
        // Aucun plan d'abonnement n'est proposé pour cette catégorie.

        // ─────────────────────────────────────────────────────
        // 4. LOCATION (VEHICULE AVEC CHAUFFEUR) - ID 3 → Commission pure, pas d'abonnement
        // ─────────────────────────────────────────────────────
        // La commission est définie au niveau du service_type (commission_percentage).
        // Aucun plan d'abonnement n'est proposé pour cette catégorie.

        // ─────────────────────────────────────────────────────
        // 5. URGENCE (AMBULANCE/REMORQUAGE) - ID 5
        // ─────────────────────────────────────────────────────
        // Pas d'abonnement payant, marché ponctuel à forte valeur.
        SubscriptionPlan::create([
            'service_id' => 5,
            'name' => 'INTERVENTION URGENCE',
            'description' => 'Aucun frais d\'abonnement pour les services d\'urgence. Commission au succès.',
            'price' => 0,
            'period' => 'MONTHLY',
            'commission_type' => 'percentage',
            'commission_value' => 20.00, // 20% de commission
            'priority' => 100,
            'priority_weight' => 50,
            'max_categories' => 3,
            'status' => 'active'
        ]);
    }
}
