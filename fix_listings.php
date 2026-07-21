<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\MarketplaceCategory::count();
echo "Total categories: $count\n";

\App\Models\MarketplaceListing::withoutEvents(function () {
    $listings = \App\Models\MarketplaceListing::all();
    $updated = 0;
    foreach ($listings as $listing) {
        $c = strtolower($listing->category);
        if (strpos($c, 'véhicule') !== false || strpos($c, 'voiture') !== false || strpos($c, 'auto') !== false || strpos($c, 'vehicles') !== false) {
            $listing->category = 'VEHICLES';
        } elseif (strpos($c, 'immo') !== false || strpos($c, 'real_estate') !== false) {
            $listing->category = 'REAL_ESTATE';
        } elseif (strpos($c, 'service') !== false || strpos($c, 'emploi') !== false || strpos($c, 'services') !== false) {
            $listing->category = 'SERVICES';
        } elseif (strpos($c, 'billet') !== false || strpos($c, 'event') !== false || strpos($c, 'tickets') !== false) {
            $listing->category = 'TICKETS';
        } else {
            $listing->category = 'ARTICLE';
        }
        if($listing->save()) $updated++;
    }
    echo "Annonces rattachées sans declencher les observers: $updated\n";
});

