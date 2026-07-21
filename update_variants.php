<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$inter = App\Models\ServiceType::where('name', 'Inter-communal')->first();
if ($inter) {
    $vars = $inter->allowed_variants;
    if (!in_array('prive', $vars)) {
        $vars[] = 'prive';
        $inter->allowed_variants = $vars;
        $inter->save();
        echo "Updated Inter-communal variants\n";
    }
}

$amb = App\Models\ServiceType::where('name', 'Ambulance')->first();
if ($amb) {
    $amb->allowed_variants = ['ambulance', 'depannage'];
    $amb->save();
    echo "Updated Ambulance variants\n";
}

$shareTypes = App\Models\ServiceType::whereIn('name', ['Taxi Vtc', 'Taxi Compteur', 'SUV'])->get();
foreach($shareTypes as $st) {
    $vars = $st->allowed_variants;
    if (!in_array('partage', $vars)) {
        $vars[] = 'partage';
    }
    if (!in_array('arret_pdp', $vars)) {
        $vars[] = 'arret_pdp';
    }
    $st->allowed_variants = $vars;
    $st->save();
    echo "Updated " . $st->name . " variants\n";
}
