<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== NETTOYAGE DES DOUBLONS PDP STOPS ===\n\n";

// Trouver les paires de doublons GPS (< 100m = 0.001 degrés)
$gps_dups = DB::select(
    "SELECT a.id as id1, b.id as id2, a.name as n1, b.name as n2
     FROM pdp_stops a JOIN pdp_stops b ON a.id < b.id
     WHERE ABS(a.latitude - b.latitude) < 0.001 AND ABS(a.longitude - b.longitude) < 0.001"
);

$toDelete = [];
$reassign = []; // [old_id => keep_id]

foreach ($gps_dups as $dup) {
    // On garde le plus petit ID (le plus ancien)
    $keepId   = $dup->id1;
    $deleteId = $dup->id2;

    if (!in_array($deleteId, $toDelete)) {
        $toDelete[] = $deleteId;
        $reassign[$deleteId] = $keepId;
        echo "  SUPPRIME ID#{$deleteId} [{$dup->n2}] → remplacé par ID#{$keepId} [{$dup->n1}]\n";
    }
}

echo "\nTotal à supprimer : " . count($toDelete) . "\n\n";

if (empty($toDelete)) {
    echo "Rien à nettoyer.\n";
    exit;
}

// 1. Réaffecter les pdp_route_stops qui pointent vers les doublons
echo "--- Réaffectation des pdp_route_stops ---\n";
foreach ($reassign as $oldId => $newId) {
    // Vérifier si le newId est déjà dans la même route
    $existing = DB::select(
        "SELECT prs.pdp_route_id FROM pdp_route_stops prs 
         WHERE prs.pdp_stop_id = ? 
         AND prs.pdp_route_id IN (SELECT pdp_route_id FROM pdp_route_stops WHERE pdp_stop_id = ?)",
        [$newId, $oldId]
    );
    
    if (!empty($existing)) {
        // Le newId existe déjà dans la même route → supprimer simplement la liaison de l'ancien
        $del = DB::delete("DELETE FROM pdp_route_stops WHERE pdp_stop_id = ?", [$oldId]);
        echo "  Supprimé liaison(s) de ID#{$oldId} (conflit avec ID#{$newId} sur même ligne)\n";
    } else {
        // Remplacer l'ancien ID par le nouveau dans pdp_route_stops
        $upd = DB::update("UPDATE pdp_route_stops SET pdp_stop_id = ? WHERE pdp_stop_id = ?", [$newId, $oldId]);
        echo "  Réaffecté {$upd} liaison(s) de ID#{$oldId} → ID#{$newId}\n";
    }
}

// 2. Réaffecter user_requests (pickup_stop_id, dropoff_stop_id)
echo "\n--- Réaffectation des user_requests ---\n";
foreach ($reassign as $oldId => $newId) {
    $u1 = DB::update("UPDATE user_requests SET pickup_stop_id = ? WHERE pickup_stop_id = ?", [$newId, $oldId]);
    $u2 = DB::update("UPDATE user_requests SET dropoff_stop_id = ? WHERE dropoff_stop_id = ?", [$newId, $oldId]);
    if ($u1 + $u2 > 0) echo "  Réaffecté {$u1}+{$u2} requêtes ID#{$oldId} → ID#{$newId}\n";
}

// 3. Supprimer les doublons
echo "\n--- Suppression des doublons ---\n";
foreach ($toDelete as $deleteId) {
    DB::delete("DELETE FROM pdp_stops WHERE id = ?", [$deleteId]);
    echo "  Supprimé ID#{$deleteId}\n";
}

// 4. Résumé final
$total = DB::select("SELECT COUNT(*) as cnt FROM pdp_stops")[0]->cnt;
echo "\n=== NETTOYAGE TERMINÉ ===\n";
echo "  Arrêts restants en BDD: {$total}\n";
echo "  Doublons supprimés: " . count($toDelete) . "\n";
