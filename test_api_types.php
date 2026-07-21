<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simuler un appel à getServiceTypes pour Location et Urgence
$controller = new \App\Http\Controllers\UserServiceController();

foreach (['location', 'urgence'] as $cat) {
    $request = \Illuminate\Http\Request::create('/api/user/service/types', 'GET', [
        'service_name' => $cat,
        'ride_variant' => 'prive',  // valeur par défaut envoyée par l'app
    ]);

    $response = $controller->getServiceTypes($request);
    $data = json_decode($response->getContent(), true);

    echo "=== CATÉGORIE : " . strtoupper($cat) . " (variant=prive) ===\n";
    if ($data['status'] ?? false) {
        $types = $data['service']['service_types'] ?? [];
        if (empty($types)) {
            echo "  ❌ AUCUN VÉHICULE RETOURNÉ !\n";
        } else {
            foreach ($types as $t) {
                echo "  ✅ {$t['name']} (ID:{$t['id']}) variants:" . json_encode($t['allowed_variants']) . "\n";
            }
        }
    } else {
        echo "  ❌ Erreur : " . json_encode($data) . "\n";
    }
    echo "\n";
}
