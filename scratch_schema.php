<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$schema = DB::select("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'mkt_products'
");

echo "--- COLUMNS OF mkt_products ---\n";
foreach ($schema as $col) {
    echo "{$col->column_name} ({$col->data_type}) - Nullable: {$col->is_nullable}, Default: {$col->column_default}\n";
}

$schema2 = DB::select("
    SELECT column_name, data_type, is_nullable, column_default
    FROM information_schema.columns
    WHERE table_name = 'marketplace_listings'
");

echo "\n--- COLUMNS OF marketplace_listings ---\n";
foreach ($schema2 as $col) {
    echo "{$col->column_name} ({$col->data_type}) - Nullable: {$col->is_nullable}, Default: {$col->column_default}\n";
}
