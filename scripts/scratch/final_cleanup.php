<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Service;
use Illuminate\Support\Facades\DB;

echo "=== NETTOYAGE FINAL ===\n";

// 1. Supprimer le service Livraison (puisque l'utilisateur l'a demandé)
$deleted = Service::where('name', 'Livraison')->orWhere('name', 'Delivery')->delete();
echo "Service Livraison supprimé : " . ($deleted ? "OUI ($deleted lignes)" : "NON (déjà absent)") . "\n";

// 2. Convertir le chemin du Taxi en chemin relatif
$taxi = Service::where('name', 'Taxi')->first();
if ($taxi && strpos($taxi->image, 'http') === 0) {
    // On extrait juste la partie après /uploads/
    if (preg_match('/uploads\/(.+)$/', $taxi->image, $matches)) {
        $taxi->image = 'uploads/' . $matches[1];
        $taxi->save();
        echo "Chemin de l'icône Taxi corrigé en relatif : " . $taxi->image . "\n";
    }
}

echo "=== SERVICES RESTANTS ===\n";
foreach (Service::all() as $s) {
    echo "- ID: {$s->id} | Nom: {$s->name} | Image: {$s->image}\n";
}
