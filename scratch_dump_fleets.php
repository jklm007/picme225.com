<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$fleet = \App\Models\Fleet::first();
if ($fleet) {
    print_r($fleet->toArray());
} else {
    echo "No fleet found\n";
}
