<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use App\ServiceType;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Nettoyage des ServiceTypes ===\n\n";

// Supprimer TOUS les ServiceTypes sauf les 3 ambulances
$ambulanceNames = ['Ambulance Basique', 'VHC Médicalisé (SAMU)', 'SMUR Adulte'];

echo "Suppression de tous les ServiceTypes sauf les ambulances...\n";

$deleted = ServiceType::whereNotIn('name', $ambulanceNames)->delete();

echo "✓ {$deleted} ServiceTypes supprimés\n\n";

// Vérifier ce qui reste
$remaining = ServiceType::all();
echo "ServiceTypes restants: {$remaining->count()}\n\n";

foreach ($remaining as $type) {
    echo "- ID: {$type->id} | Name: {$type->name} | Ambulance: {$type->ambulance}\n";
}

echo "\n✓ Nettoyage terminé!\n";
echo "\nMAINTENANT, les sous-types ambulance ne devraient apparaître QUE pour le service Ambulance.\n";
