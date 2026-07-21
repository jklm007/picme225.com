<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Service;

echo "=== CURRENT SERVICES IN DATABASE ===\n";
$services = Service::all();
foreach ($services as $s) {
    echo "ID: {$s->id}\n";
    echo "Name: {$s->name}\n";
    echo "Original Image Field: " . $s->getRawOriginal('image') . "\n";
    echo "Accessor image_url: " . $s->image_url . "\n";
    echo "---------------------------\n";
}
