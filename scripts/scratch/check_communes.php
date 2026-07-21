<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$communal = App\ServiceType::where('is_communal', 1)->get(['name', 'commune']);
echo "Communal Services:\n";
foreach ($communal as $s) {
    echo "- " . $s->name . " (" . ($s->commune ?: 'No commune specified') . ")\n";
}

$stops = App\PdpStop::select('commune', DB::raw('count(*) as count'))->groupBy('commune')->get();
echo "\nStops per Commune:\n";
foreach ($stops as $stop) {
    echo "- " . ($stop->commune ?: 'N/A') . ": " . $stop->count . "\n";
}
