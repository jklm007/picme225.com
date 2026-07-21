<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$listings = \App\Models\MarketplaceListing::whereIn('id', [246, 243, 240, 191, 51, 50])->select('id', 'cover_image', 'images')->get();
echo json_encode($listings, JSON_PRETTY_PRINT);
