<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;
use Illuminate\Support\Facades\DB;

// 1. Renommer les services Voyage (supprimer le suffixe)
$renames = [
    21 => 'Berline',      // was: Berline Voyage
    22 => 'SUV',          // was: SUV Voyage
    23 => 'Minibus',      // was: MiniBus Voyage
];

foreach ($renames as $id => $newName) {
    $st = ServiceType::find($id);
    if ($st) {
        echo "Renaming '{$st->name}' → '{$newName}'\n";
        $st->name = $newName;
        $st->save();

        // Mettre à jour aussi le nom dans la table pivot service_service_type
        DB::table('service_service_type')
            ->where('service_type_id', $id)
            ->update(['name' => $newName]);
    }
}

// 2. Renommer la Berline Location (id 19) en gardant un identifiant unique
// Elle est déjà "Berline" → type rental, pas de conflit si le voyage est aussi "Berline"
// mais on peut différencier par type. Laissons comme tel.

echo "\nNoms corrigés dans la BD !\n";

// 3. Afficher l'état final des services voyage et location/urgence
echo "\nÉtat final des services (hors standard):\n";
$types = ServiceType::whereIn('type', ['voyage', 'rental', 'urgence', 'livraison'])->get(['id','name','type','allowed_variants']);
foreach ($types as $t) {
    echo "  [{$t->type}] ID {$t->id} : {$t->name} — " . implode(', ', (array)$t->allowed_variants) . "\n";
}
