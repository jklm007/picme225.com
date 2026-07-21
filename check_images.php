<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- MAIN SERVICES ---\n";
$services = \App\Models\Service::all(['id', 'name', 'image']);
foreach($services as $s) {
    echo $s->id . ' - ' . $s->name . ': ' . ($s->image ?: 'NO IMAGE') . "\n";
}

echo "\n--- SERVICE TYPES ---\n";
$types = \App\Models\ServiceType::all(['id', 'name', 'image']);
foreach($types as $t) {
    echo $t->id . ' - ' . $t->name . ': IMAGE = ' . ($t->image ?: 'NO IMAGE') . "\n";
}
