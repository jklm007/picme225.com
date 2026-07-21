<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ghostTitle = "Téléphone Samsung Galaxy S22";
$ghostTitle2 = "Samsung Galaxy S22";

$deleted = \App\Models\MarketplaceListing::where('title', 'like', '%Samsung Galaxy S22%')->delete();
echo "Deleted {$deleted} ghost Samsung listings.\n";
