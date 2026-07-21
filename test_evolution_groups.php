<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$evoApiUrl = config('services.evolution.url');
$evoApiKey = config('services.evolution.key');
$instanceName = config('services.evolution.instance', 'picme225');

echo "URL: $evoApiUrl\n";
echo "Instance: $instanceName\n";

$response = Http::withHeaders(['apikey' => $evoApiKey])
    ->get("{$evoApiUrl}/group/fetchAllGroups/{$instanceName}?getParticipants=false");

if ($response->successful()) {
    $data = $response->json();
    echo "Groups found: " . count($data) . "\n";
    if (count($data) > 0) {
        echo json_encode($data[0], JSON_PRETTY_PRINT);
    }
} else {
    echo "Error: " . $response->body() . "\n";
}
