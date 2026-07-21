<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MarketplaceCategory;

MarketplaceCategory::whereIn('id', [1,2,3,4])->delete();
echo "Deleted legacy categories 1-4\n";

$all = MarketplaceCategory::all();
echo "Remaining categories: " . $all->count() . "\n";
foreach ($all as $cat) {
    echo "ID: {$cat->id} | Name: {$cat->name} | Label: {$cat->label}\n";
}
