<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\MarketplaceListing;
use Illuminate\Support\Facades\DB;

// Cleanup first
MarketplaceListing::where('title', 'LIKE', 'TEST_OBSERVER_%')->forceDelete();
DB::table('mkt_vehicles')->where('brand', 'TEST_BRAND')->delete();

echo "Testing Observer...\n";

$listing = MarketplaceListing::create([
    'user_id' => 1,
    'title' => 'TEST_OBSERVER_VEHICLE',
    'description' => 'A test vehicle to verify observer',
    'price' => 5000,
    'category' => 'VEHICLES',
    'brand' => 'TEST_BRAND',
    'model' => 'TEST_MODEL',
    'year' => '2023',
    'color' => 'Red',
    'plate_number' => 'AB-123-CD',
]);

$listing->refresh();

echo "Created Listing ID: " . $listing->id . "\n";
echo "Listable Type: " . $listing->listable_type . "\n";
echo "Listable ID: " . $listing->listable_id . "\n";

$vehicle = DB::table('mkt_vehicles')->where('id', $listing->listable_id)->first();
if ($vehicle) {
    echo "SUCCESS: Shadow vehicle created with brand: " . $vehicle->brand . "\n";
} else {
    echo "ERROR: Shadow vehicle NOT found!\n";
}

// Update the listing and see if the vehicle is updated
$listing->brand = 'TEST_BRAND_UPDATED';
$listing->save();

$vehicle = DB::table('mkt_vehicles')->where('id', $listing->listable_id)->first();
if ($vehicle && $vehicle->brand === 'TEST_BRAND_UPDATED') {
    echo "SUCCESS: Shadow vehicle updated with new brand: " . $vehicle->brand . "\n";
} else {
    echo "ERROR: Shadow vehicle NOT updated!\n";
}

// Cleanup
$listing->forceDelete();
DB::table('mkt_vehicles')->where('id', $listing->listable_id)->delete();
