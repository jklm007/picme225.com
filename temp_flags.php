<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s_types = \App\ServiceType::whereIn('name', ['UTB Express', 'SBTA Express'])->get();
foreach ($s_types as $st) {
    $st->is_intercommunal = 0;
    $st->is_interregional = 1;
    $st->save();
    echo "Updated " . $st->name . " -> is_intercommunal: " . $st->is_intercommunal . ", is_interregional: " . $st->is_interregional . "\n";
}
