<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$listings = App\Models\MarketplaceListing::whereIn('id', [272, 268, 264, 263])->get(['id', 'cover_image', 'images'])->toArray();
echo json_encode($listings, JSON_PRETTY_PRINT);
