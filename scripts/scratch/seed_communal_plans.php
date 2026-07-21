<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$now = Carbon::now();

// Plans Communaux / Partage dédiés (Service ID 6 = Partage / Wôrô-wôrô / Gbaka / Massa)
$plans = [
    // ─────────────── COMMUNAL FREE (Gratuit) ───────────────
    [
        'service_id'               => 6,
        'name'                     => 'COMMUNAL FREE',
        'description'              => 'Plan de base pour les Wôrô-wôrô & Gbaka. Variantes Partage & Arrêt PDP incluses. La course Privée nécessite un abonnement supérieur.',
        'price'                    => 0.00,
        'period'                   => 'MONTHLY',
        'commission_type'          => 'percentage',
        'commission_value'         => 10.00,   // 10% sur ticket partagé
        'fixed_fee'                => 50.00,   // 50 CFA / ticket en complément
        'max_categories'           => 1,
        'priority'                 => 10,
        'priority_weight'          => 10,
        'insurance_included'       => 0,
        'staking_bonus_percentage' => 0.00,
        'status'                   => 'active',
        'show_on_marketplace'      => 1,
        'badge_url'                => null,
        'created_at'               => $now,
        'updated_at'               => $now,
    ],

    // ─────────────── COMMUNAL ECO (Hebdomadaire) ───────────────
    [
        'service_id'               => 6,
        'name'                     => 'COMMUNAL ECO',
        'description'              => 'Débloque la variante Privée. Idéal pour les Wôrô-wôrô qui veulent augmenter leurs revenus avec des courses complètes.',
        'price'                    => 3000.00,   // 3 000 CFA / semaine
        'period'                   => 'WEEKLY',
        'commission_type'          => 'percentage',
        'commission_value'         => 8.00,       // 8% sur partagé
        'fixed_fee'                => 0.00,
        'max_categories'           => 2,
        'priority'                 => 30,
        'priority_weight'          => 30,
        'insurance_included'       => 0,
        'staking_bonus_percentage' => 0.00,
        'status'                   => 'active',
        'show_on_marketplace'      => 1,
        'badge_url'                => null,
        'created_at'               => $now,
        'updated_at'               => $now,
    ],

    // ─────────────── COMMUNAL PRO (Mensuel) ───────────────
    [
        'service_id'               => 6,
        'name'                     => 'COMMUNAL PRO',
        'description'              => 'Commission réduite à 5% sur tout. Priorité de dispatch sur les arrêts PDP. Meilleur remplissage véhicule garanti par l\'IA.',
        'price'                    => 10000.00,  // 10 000 CFA / mois
        'period'                   => 'MONTHLY',
        'commission_type'          => 'percentage',
        'commission_value'         => 5.00,
        'fixed_fee'                => 0.00,
        'max_categories'           => 3,
        'priority'                 => 60,
        'priority_weight'          => 60,
        'insurance_included'       => 0,
        'staking_bonus_percentage' => 0.00,
        'status'                   => 'active',
        'show_on_marketplace'      => 1,
        'badge_url'                => null,
        'created_at'               => $now,
        'updated_at'               => $now,
    ],

    // ─────────────── COMMUNAL GOLD (Mensuel Premium) ───────────────
    [
        'service_id'               => 6,
        'name'                     => 'COMMUNAL GOLD',
        'description'              => '0% commission variable sur les courses Privées. Priorité Absolue IA pour remplir son véhicule. +5% Bonus ECO Cashback sur chaque ticket validé.',
        'price'                    => 20000.00,  // 20 000 CFA / mois
        'period'                   => 'MONTHLY',
        'commission_type'          => 'fixed',
        'commission_value'         => 0.00,       // Zéro % variable
        'fixed_fee'                => 25.00,      // 25 CFA réseau / passager partagé
        'max_categories'           => 5,
        'priority'                 => 100,
        'priority_weight'          => 100,
        'insurance_included'       => 1,
        'staking_bonus_percentage' => 5.00,
        'status'                   => 'active',
        'show_on_marketplace'      => 1,
        'badge_url'                => null,
        'created_at'               => $now,
        'updated_at'               => $now,
    ],
];

// Eviter les doublons : supprimer les plans Communaux existants si re-run
DB::table('subscription_plans')
    ->where('service_id', 6)
    ->whereIn('name', ['COMMUNAL STANDARD', 'COMMUNAL FREE', 'COMMUNAL ECO', 'COMMUNAL PRO', 'COMMUNAL GOLD'])
    ->delete();

DB::table('subscription_plans')->insert($plans);

echo "✅ 4 plans Communaux insérés avec succès !\n\n";

echo "=== Plans Communaux en base ===\n";
$inserted = DB::table('subscription_plans')->where('service_id', 6)->get(['id', 'name', 'price', 'period', 'commission_value', 'fixed_fee']);
foreach ($inserted as $p) {
    $prix = number_format($p->price, 0, ',', ' ');
    echo "ID: {$p->id} | {$p->name} | {$prix} CFA / {$p->period} | Commission: {$p->commission_value}% | Frais fixe: {$p->fixed_fee} CFA\n";
}

echo "\n=== Parité rappel ===\n";
echo "1 ECO = 1 000 CFA\n";
echo "COMMUNAL ECO = 3 CFA ECO / semaine\n";
echo "COMMUNAL PRO = 10 ECO / mois\n";
echo "COMMUNAL GOLD = 20 ECO / mois\n";
