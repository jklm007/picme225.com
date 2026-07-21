<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s_types = \App\ServiceType::whereIn('name', ['UTB Express', 'SBTA Express'])->get();
foreach ($s_types as $st) {
    $st->image = 'service/inter-communal.png';
    $st->save();
    echo "Updated image for " . $st->name . "\n";
}
