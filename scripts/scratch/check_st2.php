<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = \App\ServiceType::find(1);
echo "NAME: " . $s->name . "\n";
echo "CALCULATOR: " . $s->calculator . "\n";
