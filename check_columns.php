<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('marketplace_listings');
print_r("marketplace_listings columns:\n");
print_r($columns);

print_r("\n==================\n");
$hasCategoryTable = \Illuminate\Support\Facades\Schema::hasTable('marketplace_categories');
print_r("Has marketplace_categories table: " . ($hasCategoryTable ? 'Yes' : 'No') . "\n");

if ($hasCategoryTable) {
    print_r(\Illuminate\Support\Facades\Schema::getColumnListing('marketplace_categories'));
    $categories = \Illuminate\Support\Facades\DB::table('marketplace_categories')->get();
    print_r($categories);
}
