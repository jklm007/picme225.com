<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;

// Clear all service caches
Cache::flush();
echo "Cache flushed\n\n";

// Simulate the exact API call that Android sends for Location+avec_chauffeur
$controller = new \App\Http\Controllers\UserServiceController();

$testCases = [
    ['service_name' => 'Location', 'ride_variant' => 'avec_chauffeur'],
    ['service_name' => 'Location', 'ride_variant' => 'sans_chauffeur'],
    ['service_name' => 'rental',   'ride_variant' => 'avec_chauffeur'],
    ['service_name' => 'rental',   'ride_variant' => 'sans_chauffeur'],
];

foreach ($testCases as $data) {
    echo "=== TEST: service_name={$data['service_name']}, ride_variant={$data['ride_variant']} ===\n";
    $req = new \Illuminate\Http\Request();
    $req->replace($data);
    
    $resp = $controller->getServiceTypes($req);
    $json = json_decode($resp->getContent(), true);
    
    if (isset($json['status']) && $json['status'] === true) {
        $types = $json['service']['service_types'] ?? [];
        echo "  Status: OK - Found " . count($types) . " service type(s)\n";
        foreach ($types as $t) {
            echo "    - [{$t['type']}] {$t['name']} | variants: " . json_encode($t['allowed_variants']) . "\n";
        }
    } else {
        echo "  Status: FAIL - " . ($json['message'] ?? 'Unknown error') . "\n";
    }
    echo "\n";
}
