<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$stops = \App\PdpStop::all();
echo "ID | Name | Status | Active | Public\n";
foreach ($stops as $s) {
    echo $s->id . " | " . $s->name . " | " . $s->status . " | " . $s->is_active . " | " . $s->is_public . "\n";
}
