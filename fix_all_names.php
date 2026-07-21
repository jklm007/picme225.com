<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;
use Illuminate\Support\Facades\DB;

// Mapping : ID => nouveau nom propre (sans préfixe de catégorie)
$renames = [
    // Standard (Taxi)
    1  => 'VTC',               // Taxi Vtc  → VTC
    2  => 'Compteur',          // Taxi Compteur → Compteur
    15 => 'Woro-Woro',         // ok, mais nettoyer si besoin
    16 => 'Navette',           // Bus Navette → Navette
    17 => '7 Places',          // PicME 7 Places → 7 Places
    // Urgence
    29 => 'Ambulance VIP',     // Ambulance Privée → Ambulance VIP (plus concis)
    // Test (sans type) : désactiver le service de test
    18 => 'Classic Test',      // Classic Test Service → Classic Test
];

foreach ($renames as $id => $newName) {
    $st = ServiceType::find($id);
    if ($st) {
        $old = $st->name;
        $st->name = $newName;
        $st->save();

        // Synchroniser le nom dans la table pivot
        DB::table('service_service_type')
            ->where('service_type_id', $id)
            ->update(['name' => $newName]);

        echo "  ✓ [{$st->type}] ID $id : '$old' → '$newName'\n";
    } else {
        echo "  ✗ ID $id non trouvé\n";
    }
}

echo "\n--- État final de tous les service types ---\n";
$types = \App\ServiceType::select('id', 'name', 'type', 'status')->orderBy('type')->orderBy('id')->get();
foreach ($types as $t) {
    $status = $t->status ? '✅' : '❌';
    echo "$status [{$t->type}] ID {$t->id} : {$t->name}\n";
}
