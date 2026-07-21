<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate exact API response for Location
$controller = new \App\Http\Controllers\UserServiceController();
$req = new \Illuminate\Http\Request();
$req->replace(['service_name' => 'Location', 'ride_variant' => 'avec_chauffeur']);

$resp = $controller->getServiceTypes($req);
$json = json_decode($resp->getContent(), true);

$types = $json['service']['service_types'] ?? [];
echo "=== JSON renvoyé par l'API pour chaque service type ===\n\n";
foreach ($types as $t) {
    echo "--- {$t['name']} (ID: {$t['id']}) ---\n";
    // Show all image-related keys
    foreach ($t as $k => $v) {
        if (stripos($k, 'image') !== false || stripos($k, 'icon') !== false || stripos($k, 'photo') !== false) {
            $display = is_string($v) ? $v : json_encode($v);
            echo "  {$k} = " . (empty($v) ? '(VIDE/NULL)' : $display) . "\n";
        }
    }
    echo "\n";
}

echo "=== DIAGNOSTIC FINAL ===\n";
echo "Android cherche: 'image_url' (SerializedName)\n";
echo "API renvoie: ";
$keys = array_keys($types[0] ?? []);
$imgKeys = array_filter($keys, fn($k) => stripos($k, 'image') !== false);
echo implode(', ', $imgKeys) ?: 'AUCUN champ image!';
echo "\n";
