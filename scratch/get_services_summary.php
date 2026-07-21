<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$serviceTypes = \App\Models\ServiceType::all();

echo "--- TOTAL SERVICE TYPES IN DATABASE: " . $serviceTypes->count() . " ---\n\n";

foreach ($serviceTypes as $st) {
    echo "ID: {$st->id}\n";
    echo "Name: {$st->name}\n";
    echo "Type: {$st->type}\n";
    echo "Allowed Variants: " . json_encode($st->allowed_variants) . "\n";
    echo "Status: {$st->status}\n";
    echo "Rental Amount: {$st->rental_amount}\n";
    echo "Allow Without Driver: " . ($st->allow_without_driver ? 'YES' : 'NO') . "\n";
    
    // Check if it has rental prices associated in service_type_rentals
    $rentals = $st->service()->with('package')->get();
    if ($rentals->isNotEmpty()) {
        echo "Rental Packages (service_type_rentals):\n";
        foreach ($rentals as $r) {
            $package = $r->package;
            $pkgStr = $package ? "{$package->hour}h / {$package->kilometer}km" : "Unknown package ID {$r->km_hour_id}";
            echo "  - Package [{$pkgStr}]: Price {$r->ren_price} CFA\n";
        }
    }
    
    // Check if it has prices in km_hour_service_type_prices
    $kmHourPrices = \Illuminate\Support\Facades\DB::table('km_hour_service_type_prices')
        ->where('service_type_id', $st->id)
        ->get();
    if ($kmHourPrices->isNotEmpty()) {
        echo "KM/Hour Prices (km_hour_service_type_prices):\n";
        foreach ($kmHourPrices as $khp) {
            $package = \App\Models\KmHour::find($khp->km_hour_id);
            $pkgStr = $package ? "{$package->hour}h / {$package->kilometer}km" : "Unknown package ID {$khp->km_hour_id}";
            echo "  - Package [{$pkgStr}]: Price {$khp->price} CFA\n";
        }
    }
    
    echo "--------------------------------------------------------\n";
}
