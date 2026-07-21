<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \DB::getSchemaBuilder()->getColumnListing('pdp_route_stops');
echo "Columns of pdp_route_stops:\n";
print_r($columns);

$firstRow = \DB::table('pdp_route_stops')->first();
if ($firstRow) {
    echo "First row:\n";
    print_r((array)$firstRow);
} else {
    echo "No rows in pdp_route_stops\n";
}
