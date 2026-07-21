<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$categories = [
    ['name' => 'VEHICLES', 'label' => 'Véhicules', 'icon' => '🚗', 'order_index' => 1],
    ['name' => 'ARTICLE', 'label' => 'Articles', 'icon' => '📦', 'order_index' => 2],
    ['name' => 'REAL_ESTATE', 'label' => 'Immobilier', 'icon' => '🏠', 'order_index' => 3],
    ['name' => 'TICKETS', 'label' => 'Billets', 'icon' => '🎫', 'order_index' => 4],
    ['name' => 'SERVICES', 'label' => 'Services', 'icon' => '🛠️', 'order_index' => 5],
];

foreach($categories as $cat) {
    \App\Models\MarketplaceCategory::updateOrCreate(['name' => $cat['name']], $cat);
}
echo "Marketplace categories seeded successfully.\n";
