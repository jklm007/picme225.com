<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\MarketplaceCategory::truncate();
App\Models\MarketplaceCategory::insert([
    ['name' => 'SALE', 'label' => 'Vente', 'icon' => '🛍️'],
    ['name' => 'RENTAL', 'label' => 'Location', 'icon' => '🔑'],
    ['name' => 'SERVICE_CALL', 'label' => 'Service', 'icon' => '🛠️'],
    ['name' => 'TICKET', 'label' => 'Billet', 'icon' => '🎫']
]);
echo "Categories seeded successfully.\n";
