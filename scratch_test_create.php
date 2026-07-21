<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MktProduct;

try {
    echo "Creating MktProduct...\n";
    $p = new MktProduct();
    $p->stock_quantity = 5;
    $p->save();
    echo "Created successfully! ID: {$p->id}\n";
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
