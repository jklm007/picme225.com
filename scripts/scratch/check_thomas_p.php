<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;

$p = Provider::find(2);
echo "Thomas (ID 2) detail:\n";
echo "service_type_id: " . ($p->service_type_id ?? 'NULL') . "\n";
echo "status: {$p->status}\n";
echo "available: " . ($p->available ? 'Yes' : 'No') . "\n";
