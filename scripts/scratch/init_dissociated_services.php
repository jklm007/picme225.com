<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Service;
use Illuminate\Support\Facades\DB;

echo "=== INITIALIZING SERVICES DISSOCIATION ===\n";

// Ensure 'Taxi' exists
$taxi = Service::firstOrCreate(['name' => 'Taxi'], ['image' => 'standard.jpg']);
echo "Service [Taxi] ensured (ID: {$taxi->id})\n";

// Check if 'Livraison' exists, if not create it
$livraison = Service::where('name', 'Livraison')->orWhere('name', 'Delivery')->first();
if (!$livraison) {
    $livraison = Service::create(['name' => 'Livraison', 'image' => 'service/delivery_main.png']);
    echo "Service [Livraison] created (ID: {$livraison->id})\n";
} else {
    // Force name to French if it was Delivery
    $livraison->name = 'Livraison';
    $livraison->save();
    echo "Service [Livraison] already exists (ID: {$livraison->id})\n";
}

// Optional: clean up other unwanted categories if the user only wants these two
// For now, let's keep it safe.

echo "=== CATEGORIES IN DB ===\n";
foreach (Service::all() as $s) {
    echo "- ID: {$s->id} | Name: {$s->name} | Image: {$s->image}\n";
}

echo "\nNote: All placeholders in Android have been removed. \nThe app will now use the icons you upload in the dashboard for these categories.\n";
