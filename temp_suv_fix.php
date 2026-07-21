<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$suvs = \App\Models\ServiceType::where('name', 'SUV')->get();
foreach ($suvs as $suv) {
    if (in_array('avec_chauffeur', $suv->allowed_variants ?? [])) {
        $suv->allowed_variants = ['avec_chauffeur'];
    } else {
        $suv->allowed_variants = ['prive'];
    }
    $suv->save();
    echo "Fixed SUV " . $suv->id . " allowed_variants to " . json_encode($suv->allowed_variants) . "\n";
}

$berlines = \App\Models\ServiceType::where('name', 'Berline')->get();
foreach ($berlines as $b) {
    if (in_array('avec_chauffeur', $b->allowed_variants ?? [])) {
        $b->allowed_variants = ['avec_chauffeur'];
    } else {
        $b->allowed_variants = ['prive'];
    }
    $b->save();
    echo "Fixed Berline " . $b->id . " allowed_variants to " . json_encode($b->allowed_variants) . "\n";
}
