<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Service;
use App\ServiceType;
use Illuminate\Support\Facades\DB;

echo "=== DISSOCIATION & NETTOYAGE DES SERVICES ===\n\n";

// 1. Récupérer les catégories
$taxi = Service::where('name', 'Taxi')->first();
$livraison = Service::where('name', 'Livraison')->first();

echo "Catégorie Taxi     : ID={$taxi->id}\n";
echo "Catégorie Livraison: ID={$livraison->id}\n\n";

// 2. Supprimer toutes les associations Livraison vers des types Taxi
echo "=== VÉRIFICATION ASSOCIATIONS ACTUELLES ===\n";
$allAssociations = DB::table('service_service_type')
    ->join('service_types', 'service_service_type.service_type_id', '=', 'service_types.id')
    ->join('services', 'service_service_type.service_id', '=', 'services.id')
    ->select('service_service_type.*', 'service_types.name as type_name', 'services.name as service_cat_name', 'service_types.type as type_type')
    ->get();

foreach ($allAssociations as $assoc) {
    echo "  [{$assoc->service_cat_name}] → [{$assoc->type_name}] (type={$assoc->type_type})\n";
}

echo "\n=== NETTOYAGE DES MAUVAISES ASSOCIATIONS ===\n";

// Supprimer des associations de types 'standard' (Taxi) qui seraient liées à Livraison
$removed = DB::table('service_service_type')
    ->where('service_id', $livraison->id)
    ->whereIn('service_type_id', function($q) {
        $q->select('id')->from('service_types')->where('type', 'standard');
    })
    ->delete();
echo "Associations Taxi→Livraison supprimées: $removed\n";

// Supprimer des associations de types 'livraison' qui seraient liées à Taxi
$removed2 = DB::table('service_service_type')
    ->where('service_id', $taxi->id)
    ->whereIn('service_type_id', function($q) {
        $q->select('id')->from('service_types')->where('type', 'livraison');
    })
    ->delete();
echo "Associations Livraison→Taxi supprimées: $removed2\n";

// 3. Créer les types de livraison s'ils n'existent pas
echo "\n=== CRÉATION TYPES DE LIVRAISON ===\n";

$livraisonTypes = [
    [
        'name'                  => 'Livraison Express Moto',
        'type'                  => 'livraison',
        'provider_name'         => 'Livreur Moto',
        'image'                 => 'service/livraison_moto.png',
        'capacity'              => 1,
        'fixed'                 => 500,
        'price'                 => 150,
        'minute'                => 0,
        'distance'              => 1,
        'calculator'            => 'DISTANCE',
        'commission_percentage' => 15,
        'status'                => 1,
        'description'           => 'Livraison rapide de petits colis par moto. Délai < 30 min.',
    ],
    [
        'name'                  => 'Livraison Voiture',
        'type'                  => 'livraison',
        'provider_name'         => 'Livreur Voiture',
        'image'                 => 'service/livraison_voiture.png',
        'capacity'              => 1,
        'fixed'                 => 1000,
        'price'                 => 250,
        'minute'                => 0,
        'distance'              => 1,
        'calculator'            => 'DISTANCE',
        'commission_percentage' => 15,
        'status'                => 1,
        'description'           => 'Livraison de colis moyens à grands par voiture.',
    ],
    [
        'name'                  => 'Livraison Van',
        'type'                  => 'livraison',
        'provider_name'         => 'Livreur Van',
        'image'                 => 'service/livraison_van.png',
        'capacity'              => 1,
        'fixed'                 => 2000,
        'price'                 => 400,
        'minute'                => 0,
        'distance'              => 1,
        'calculator'            => 'DISTANCE',
        'commission_percentage' => 15,
        'status'                => 1,
        'description'           => 'Livraison de marchandises volumineuses par van.',
    ],
];

foreach ($livraisonTypes as $data) {
    $existing = ServiceType::where('name', $data['name'])->first();
    if ($existing) {
        echo "  [EXISTE] {$data['name']} (ID:{$existing->id})\n";
        $st = $existing;
    } else {
        $st = ServiceType::create($data);
        echo "  [CRÉÉ] {$data['name']} (ID:{$st->id})\n";
    }

    // Associer à Livraison seulement
    DB::table('service_service_type')->updateOrInsert(
        ['service_id' => $livraison->id, 'service_type_id' => $st->id],
        [
            'name'          => $data['name'],
            'fixed'         => $data['fixed'],
            'price'         => $data['price'],
            'minute'        => 0,
            'distance'      => 1,
            'calculator'    => 'DISTANCE',
            'status'        => 1,
            'ambulance'     => 0,
            'rental_amount' => 0,
            'outstation_price' => 0,
            'updated_at'    => now(),
            'created_at'    => now(),
        ]
    );
    echo "    → Associé à Livraison\n";
}

echo "\n=== RÉSULTAT FINAL ===\n";
$final = DB::table('service_service_type')
    ->join('service_types', 'service_service_type.service_type_id', '=', 'service_types.id')
    ->join('services', 'service_service_type.service_id', '=', 'services.id')
    ->select('services.name as categorie', 'service_types.name as type', 'service_types.type as famille')
    ->orderBy('service_service_type.service_id')
    ->get();

foreach ($final as $row) {
    echo "  [{$row->categorie}] → {$row->type} (famille: {$row->famille})\n";
}

echo "\n✅ Terminé. Taxi et Livraison sont maintenant dissociés.\n";
