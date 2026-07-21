<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Service;

echo "=== TEST RÉPONSE API /api/user/services ===\n\n";

$services = Service::all();

$result = $services->map(function($service) {
    return [
        'id'        => $service->id,
        'name'      => $service->name,
        'image_url' => $service->image_url,
    ];
});

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo "\n\n✅ L'app Android recevra désormais cette structure.\n";
echo "   Le champ 'image_url' sera utilisé par ServicesAdapter pour charger les icônes.\n";
