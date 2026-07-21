<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Mettre à jour les lignes existantes qui sont en réalité inter-communales
$interCommunalIds = [13, 14, 15, 19, 20];
App\Models\PdpRoute::whereIn('id', $interCommunalIds)->update([
    'type' => 'INTER_COMMUNAL',
    'is_intercommunal' => 1
]);

// 2. Mettre à jour Abidjan - Bouaké pour être purement REGIONAL
App\Models\PdpRoute::where('id', 39)->update([
    'type' => 'REGIONAL'
]);

// 3. Créer les lignes inter-régionales populaires
$routes = [
    [
        'name' => 'Abidjan ↔ Yamoussoukro',
        'type' => 'REGIONAL',
        'service_class' => 'NORMAL',
        'status' => 'APPROVED',
        'base_price_per_segment' => 4000.00,
        'description' => 'Ligne express inter-régionale',
        'is_active' => 1
    ],
    [
        'name' => 'Abidjan ↔ San-Pédro',
        'type' => 'REGIONAL',
        'service_class' => 'NORMAL',
        'status' => 'APPROVED',
        'base_price_per_segment' => 7000.00,
        'description' => 'Ligne express inter-régionale côtière',
        'is_active' => 1
    ],
    [
        'name' => 'Abidjan ↔ Korhogo',
        'type' => 'REGIONAL',
        'service_class' => 'NORMAL',
        'status' => 'APPROVED',
        'base_price_per_segment' => 10000.00,
        'description' => 'Ligne express Nord',
        'is_active' => 1
    ],
    [
        'name' => 'Abidjan ↔ Daloa',
        'type' => 'REGIONAL',
        'service_class' => 'NORMAL',
        'status' => 'APPROVED',
        'base_price_per_segment' => 6000.00,
        'description' => 'Ligne express Centre-Ouest',
        'is_active' => 1
    ]
];

foreach ($routes as $routeData) {
    App\Models\PdpRoute::firstOrCreate(['name' => $routeData['name']], $routeData);
}

echo "Mise à jour réussie !\n";
