<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;

$st = ServiceType::find(1);
echo "ServiceType 1:\n";
echo "Name: {$st->name}\n";
echo "Communal: " . ($st->is_communal ? 'Yes' : 'No') . "\n";
echo "Status: " . ($st->status ? 'Active' : 'Inactive') . "\n";
echo "Fixed: {$st->fixed}\n";
echo "Price: {$st->price}\n";
