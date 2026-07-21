<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = App\Service::all();
echo "SERVICES:\n";
foreach($services as $s) {
    echo "ID: " . $s->id . " - Name: " . $s->name . "\n";
}
