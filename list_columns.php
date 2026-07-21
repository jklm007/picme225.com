<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\Schema::getColumnListing('service_types');
file_put_contents('service_types_columns.json', json_encode($columns, JSON_PRETTY_PRINT));
echo "Done.";
