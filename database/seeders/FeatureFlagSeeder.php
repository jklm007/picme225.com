<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Uses updateOrInsert (upsert on `key`) so it is safe to run multiple times.
     */
    public function run(): void
    {
        $now = now();

        $flags = [
            // ── Service flags ────────────────────────────────────────────────
            [
                'key'                  => 'taxi_enabled',
                'label'                => 'Service Taxi',
                'category'             => 'service',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            [
                'key'                  => 'delivery_enabled',
                'label'                => 'Service Livraison',
                'category'             => 'service',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            [
                'key'                  => 'rental_enabled',
                'label'                => 'Service Location',
                'category'             => 'service',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            [
                'key'                  => 'emergency_enabled',
                'label'                => 'Service Urgence',
                'category'             => 'service',
                'is_enabled'           => false,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            [
                'key'                  => 'subscription_required_premium',
                'label'                => 'Abonnement requis (Premium)',
                'category'             => 'service',
                'is_enabled'           => false,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            [
                'key'                  => 'waitlist_enabled',
                'label'                => "Liste d'Attente",
                'category'             => 'service',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            // ── Payment flags ─────────────────────────────────────────────────
            [
                'key'                  => 'surge_pricing_enabled',
                'label'                => 'Tarification Dynamique',
                'category'             => 'payment',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
            // ── General flags ─────────────────────────────────────────────────
            [
                'key'                  => 'dispatch_v2_enabled',
                'label'                => 'Algorithme Dispatch V2 (Score IA)',
                'category'             => 'general',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => 'Active le moteur de dispatch basé sur le score composite S=(D×0.40)+(A×0.25)+(R×0.15)+(P×0.10)+(S×0.10)',
            ],
            [
                'key'                  => 'auto_activation_enabled',
                'label'                => 'Activation Automatique par Capacité',
                'category'             => 'general',
                'is_enabled'           => true,
                'zone'                 => '*',
                'activation_conditions'=> null,
                'description'          => null,
            ],
        ];

        foreach ($flags as $flag) {
            DB::table('feature_flags')->updateOrInsert(
                ['key' => $flag['key']],
                array_merge($flag, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }

        $this->command->info('[FeatureFlagSeeder] ' . count($flags) . ' feature flags seeded successfully.');
    }
}
