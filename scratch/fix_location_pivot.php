<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

echo "=== Nettoyage du pivot service_service_type pour Location ===\n\n";

// Find the Location service ID
$locationService = DB::table('services')->where('name', 'Location')->first();
if (!$locationService) {
    echo "ERREUR: Service 'Location' introuvable.\n";
    exit(1);
}
echo "Service Location ID: {$locationService->id}\n\n";

// Find wrong service_types linked to Location (not of type 'rental')
$wrongLinks = DB::table('service_service_type as sst')
    ->join('service_types as st', 'st.id', '=', 'sst.service_type_id')
    ->where('sst.service_id', $locationService->id)
    ->where('st.type', '!=', 'rental')
    ->select('sst.service_type_id', 'st.name', 'st.type')
    ->get();

if ($wrongLinks->isEmpty()) {
    echo "✅ Aucune entrée erronée à corriger.\n";
} else {
    echo "Entrées à supprimer:\n";
    foreach ($wrongLinks as $w) {
        echo "  - ServiceType ID:{$w->service_type_id} ({$w->name}, type={$w->type})\n";
    }

    echo "\nSuppression en cours...\n";
    $wrongIds = $wrongLinks->pluck('service_type_id')->toArray();

    $deleted = DB::table('service_service_type')
        ->where('service_id', $locationService->id)
        ->whereIn('service_type_id', $wrongIds)
        ->delete();

    echo "✅ {$deleted} entrée(s) supprimée(s) du pivot.\n";

    // Clear all relevant caches
    Cache::flush();
    echo "✅ Cache vidé.\n";
}

echo "\n=== État final du pivot pour Location ===\n";
$remaining = DB::table('service_service_type as sst')
    ->join('service_types as st', 'st.id', '=', 'sst.service_type_id')
    ->where('sst.service_id', $locationService->id)
    ->select('sst.service_type_id', 'st.name', 'st.type', 'st.allowed_variants')
    ->get();

foreach ($remaining as $r) {
    echo "  ✅ ID:{$r->service_type_id} | {$r->name} | type={$r->type} | variants={$r->allowed_variants}\n";
}

echo "\n=== Vérification API après nettoyage ===\n";
$controller = new \App\Http\Controllers\UserServiceController();
$req = new \Illuminate\Http\Request();
$req->replace(['service_name' => 'Location', 'ride_variant' => 'avec_chauffeur']);
$resp = $controller->getServiceTypes($req);
$json = json_decode($resp->getContent(), true);
$types = $json['service']['service_types'] ?? [];
echo "API retourne maintenant " . count($types) . " service(s) pour Location:\n";
foreach ($types as $t) {
    echo "  ✅ [{$t['type']}] {$t['name']} | variants: " . json_encode($t['allowed_variants']) . "\n";
}
