<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== NETTOYAGE PLANS LOCATION & VOYAGE ===\n\n";

// 1. Supprimer tous les plans des catégories Commission Pure
$deleted = DB::table('subscription_plans')
    ->whereIn('service_id', [3, 4])
    ->delete();

echo "[OK] {$deleted} plan(s) supprime(s) pour Location (ID 3) et Voyage (ID 4).\n\n";

// 2. Vérification : plans restants par service
echo "=== Plans d'abonnement restants en base ===\n";
$plans = DB::table('subscription_plans')
    ->select('service_id', DB::raw('COUNT(*) as total'), DB::raw('GROUP_CONCAT(name ORDER BY price ASC SEPARATOR " | ") as noms'))
    ->groupBy('service_id')
    ->orderBy('service_id')
    ->get();

if ($plans->isEmpty()) {
    echo "  Aucun plan trouve en base.\n";
} else {
    foreach ($plans as $p) {
        $label = match((int)$p->service_id) {
            1 => 'TAXI',
            2 => 'LIVRAISON',
            3 => 'LOCATION',
            4 => 'VOYAGE',
            5 => 'URGENCE',
            6 => 'COMMUNAL/PARTAGE',
            default => 'SERVICE ID ' . $p->service_id,
        };
        echo "  [{$label}] {$p->total} plan(s) : {$p->noms}\n";
    }
}

echo "\n[INFO] Location (ID 3) et Voyage (ID 4) : COMMISSION PURE — aucun plan d'abonnement.\n";
echo "[INFO] Le SubscriptionController retourne desormais 'is_commission_only: true' pour ces categories.\n";
echo "\n[SUCCES] Nettoyage termine !\n";
