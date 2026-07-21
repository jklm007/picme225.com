<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$types = App\ServiceType::all();
foreach($types as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Allowed: " . json_encode($t->allowed_variants) . "\n";
}
