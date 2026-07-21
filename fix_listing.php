<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix listing #20 category and sub_category to real DB values
$listing = \App\Models\MarketplaceListing::find(20);
if ($listing) {
    $listing->category = 'VEHICLES';
    $listing->sub_category = 'VEHICLES_VOITURE';
    $listing->save();
    echo "Updated listing #20: category={$listing->category}, sub_category={$listing->sub_category}\n";
} else {
    echo "Listing 20 not found\n";
}
