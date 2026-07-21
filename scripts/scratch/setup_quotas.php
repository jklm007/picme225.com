<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MISE EN PLACE DES QUOTAS ET PLANS ===\n\n";

// ─────────────────────────────────────────────────────
// 2. Désactiver plans LOCATION et VOYAGE
// ─────────────────────────────────────────────────────
echo "2. Désactivation plans Location/Voyage...\n";
$n = DB::table('subscription_plans')->whereIn('id', [69, 70, 71])->update([
    'status'           => 'inactive',
    'daily_trip_quota' => 0,
]);
echo "   ✔ {$n} plan(s) désactivé(s)\n";

// ─────────────────────────────────────────────────────
// 3. Plans TAXI / VTC
// ─────────────────────────────────────────────────────
echo "\n3. Plans TAXI/VTC...\n";

$taxiFree = DB::table('subscription_plans')->where('name', 'TAXI FREE')->first();
if (!$taxiFree) {
    DB::table('subscription_plans')->insert([
        'name'             => 'TAXI FREE',
        'description'      => 'Plan gratuit — 5 courses offertes/jour, au-delà commission 25%',
        'price'            => 0,
        'period'           => 'DAILY',        // valeur ENUM valide
        'commission_type'  => 'percentage',
        'commission_value' => 25,
        'daily_trip_quota' => 5,
        'status'           => 'active',
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
    echo "   ✔ TAXI FREE créé    → 5 courses/j | 25%\n";
} else {
    DB::table('subscription_plans')->where('id', $taxiFree->id)->update(['daily_trip_quota' => 5, 'status' => 'active']);
    echo "   ✔ TAXI FREE mis à jour → 5 courses/j\n";
}
DB::table('subscription_plans')->where('id', 66)->update(['daily_trip_quota' => 12,   'status' => 'active']);
echo "   ✔ TAXI ECO  → 12 courses/j | actif\n";
DB::table('subscription_plans')->where('id', 67)->update(['daily_trip_quota' => 25,   'status' => 'active']);
echo "   ✔ TAXI PRO  → 25 courses/j | actif\n";
DB::table('subscription_plans')->where('id', 68)->update(['daily_trip_quota' => 9999, 'status' => 'active']);
echo "   ✔ TAXI GOLD → ∞ illimité  | actif\n";

// ─────────────────────────────────────────────────────
// 4. Plans COMMUNAL
// ─────────────────────────────────────────────────────
echo "\n4. Plans COMMUNAL...\n";
DB::table('subscription_plans')->where('id', 77)->update(['daily_trip_quota' => 20,   'status' => 'active']);
echo "   ✔ COMMUNAL FREE → 20 courses/j  | actif\n";
DB::table('subscription_plans')->where('id', 78)->update(['daily_trip_quota' => 50,   'status' => 'active']);
echo "   ✔ COMMUNAL ECO  → 50 courses/j  | actif\n";
DB::table('subscription_plans')->where('id', 79)->update(['daily_trip_quota' => 100,  'status' => 'active']);
echo "   ✔ COMMUNAL PRO  → 100 courses/j | actif\n";
DB::table('subscription_plans')->where('id', 80)->update(['daily_trip_quota' => 9999, 'status' => 'active']);
echo "   ✔ COMMUNAL GOLD → ∞ illimité   | actif\n";

// ─────────────────────────────────────────────────────
// 5. Plans LIVREUR
// ─────────────────────────────────────────────────────
echo "\n5. Plans LIVREUR...\n";
$livreurFree = DB::table('subscription_plans')->where('name', 'LIVREUR FREE')->first();
if (!$livreurFree) {
    DB::table('subscription_plans')->insert([
        'name'             => 'LIVREUR FREE',
        'description'      => 'Plan gratuit — 5 livraisons offertes/jour, au-delà commission 25%',
        'price'            => 0,
        'period'           => 'DAILY',
        'commission_type'  => 'percentage',
        'commission_value' => 25,
        'daily_trip_quota' => 5,
        'status'           => 'active',
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
    echo "   ✔ LIVREUR FREE créé    → 5 livraisons/j | 25%\n";
} else {
    DB::table('subscription_plans')->where('id', $livreurFree->id)->update(['daily_trip_quota' => 5, 'status' => 'active']);
    echo "   ✔ LIVREUR FREE mis à jour → 5 livraisons/j\n";
}
DB::table('subscription_plans')->where('id', 63)->update(['daily_trip_quota' => 15,   'status' => 'active']);
echo "   ✔ LIVREUR ECO  → 15 livraisons/j | actif\n";
DB::table('subscription_plans')->where('id', 64)->update(['daily_trip_quota' => 30,   'status' => 'active']);
echo "   ✔ LIVREUR PRO  → 30 livraisons/j | actif\n";
DB::table('subscription_plans')->where('id', 65)->update(['daily_trip_quota' => 9999, 'status' => 'active']);
echo "   ✔ LIVREUR GOLD → ∞ illimité      | actif\n";

// ─────────────────────────────────────────────────────
// 6. Commission fixe Location (25%) et Voyage (20%)
// ─────────────────────────────────────────────────────
echo "\n6. Commission service_types...\n";
$nRental = DB::table('service_types')->where('type', 'rental')->update(['commission_percentage' => 25]);
echo "   ✔ LOCATION  (rental) → 25% [{$nRental} service(s)]\n";
$nVoyage = DB::table('service_types')->where('is_intercity', 1)->update(['commission_percentage' => 20]);
echo "   ✔ VOYAGE (intercity) → 20% [{$nVoyage} service(s)]\n";

// ─────────────────────────────────────────────────────
// 7. Résumé
// ─────────────────────────────────────────────────────
echo "\n=== RÉSUMÉ PLANS ACTIFS ===\n";
$plans = DB::table('subscription_plans')
    ->where('status', 'active')
    ->orderBy('name')
    ->get(['name', 'price', 'commission_value', 'daily_trip_quota']);

foreach ($plans as $p) {
    $quota = $p->daily_trip_quota >= 9999 ? '∞' : "{$p->daily_trip_quota}/j";
    echo sprintf("  %-32s %6s CFA  |  %3s%%  |  Quota: %s\n",
        $p->name, number_format($p->price, 0, ',', ' '), $p->commission_value, $quota);
}

echo "\n✅ Configuration terminée avec succès !\n";
