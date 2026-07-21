<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MarketplacePlansSeeder
 *
 * Seeds fixed-price Marketplace subscription plans for sellers and merchants.
 * These plans allow users to publish listings, products, services and ads on
 * the PickMe225 Marketplace module.
 *
 * Plans are stored in `subscription_plans` with target = 'marketplace'.
 *
 * Tiers:
 *  - GRATUIT   : Free tier — 1 active listing, basic features
 *  - STARTER   : 2 500 CFA/month — 5 listings + basic promotion
 *  - PRO       : 8 000 CFA/month — 20 listings + statistics + priority
 *  - BUSINESS  : 20 000 CFA/month — unlimited + full analytics + badge
 *
 * Run: php artisan db:seed --class=MarketplacePlansSeeder
 */
class MarketplacePlansSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $plans = [
            // ── GRATUIT (Free tier) ─────────────────────────────────────────
            [
                'name'                     => 'GRATUIT',
                'target'                   => 'marketplace',
                'service_id'               => null,
                'description'              => 'Compte vendeur gratuit. Publiez 1 annonce active à la fois. Idéal pour tester la plateforme.',
                'badge_url'                => null,
                'price'                    => 0.00,
                'period'                   => 'MONTHLY',
                'commission_type'          => 'percentage',
                'commission_value'         => 10.00, // 10% commission on sales
                'fixed_fee'                => 0.00,
                'priority'                 => 100,
                'priority_weight'          => 0,
                'insurance_included'       => false,
                'staking_bonus_percentage' => 0.00,
                'show_on_marketplace'      => true,
                'max_categories'           => 1,
                'status'                   => 'active',
                'created_at'               => $now,
                'updated_at'               => $now,
            ],

            // ── STARTER ─────────────────────────────────────────────────────
            [
                'name'                     => 'STARTER',
                'target'                   => 'marketplace',
                'service_id'               => null,
                'description'              => 'Débutez votre boutique en ligne. Jusqu\'à 5 annonces actives. Mise en avant basique dans les résultats de recherche. Commission réduite à 7%.',
                'badge_url'                => null,
                'price'                    => 2500.00,
                'period'                   => 'MONTHLY',
                'commission_type'          => 'percentage',
                'commission_value'         => 7.00,
                'fixed_fee'                => 0.00,
                'priority'                 => 300,
                'priority_weight'          => 30,
                'insurance_included'       => false,
                'staking_bonus_percentage' => 0.00,
                'show_on_marketplace'      => true,
                'max_categories'           => 2,
                'status'                   => 'active',
                'created_at'               => $now,
                'updated_at'               => $now,
            ],

            // ── PRO ─────────────────────────────────────────────────────────
            [
                'name'                     => 'PRO',
                'target'                   => 'marketplace',
                'service_id'               => null,
                'description'              => 'Développez votre business. Jusqu\'à 20 annonces actives. Statistiques de ventes détaillées. Boost prioritaire. Badge "Vendeur Pro". Commission à 5% seulement.',
                'badge_url'                => null,
                'price'                    => 8000.00,
                'period'                   => 'MONTHLY',
                'commission_type'          => 'percentage',
                'commission_value'         => 5.00,
                'fixed_fee'                => 0.00,
                'priority'                 => 700,
                'priority_weight'          => 70,
                'insurance_included'       => true,
                'staking_bonus_percentage' => 1.00,
                'show_on_marketplace'      => true,
                'max_categories'           => 5,
                'status'                   => 'active',
                'created_at'               => $now,
                'updated_at'               => $now,
            ],

            // ── BUSINESS ────────────────────────────────────────────────────
            [
                'name'                     => 'BUSINESS',
                'target'                   => 'marketplace',
                'service_id'               => null,
                'description'              => 'Solution entreprise illimitée. Annonces illimitées dans toutes les catégories. Analytics avancés. Accès API partenaire. Badge "Partenaire Officiel". Commission ultra-réduite à 3%.',
                'badge_url'                => null,
                'price'                    => 20000.00,
                'period'                   => 'MONTHLY',
                'commission_type'          => 'percentage',
                'commission_value'         => 3.00,
                'fixed_fee'                => 0.00,
                'priority'                 => 2000,
                'priority_weight'          => 150,
                'insurance_included'       => true,
                'staking_bonus_percentage' => 3.00,
                'show_on_marketplace'      => true,
                'max_categories'           => 99, // unlimited
                'status'                   => 'active',
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['name' => $plan['name'], 'target' => 'marketplace'],
                $plan
            );
        }

        $this->command->info('MarketplacePlansSeeder: ' . count($plans) . ' plans Marketplace créés/mis à jour.');
    }
}
