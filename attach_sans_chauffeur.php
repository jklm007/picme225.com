<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = App\Models\Service::where('name', 'location')->orWhere('name', 'rental')->first();
if ($service) {
    echo "Service ID: " . $service->id . "\n";
    $sansTypes = App\Models\ServiceType::where('name', 'like', '%sans chauffeur%')->get();
    
    foreach($sansTypes as $st) {
        $exists = \DB::table('service_service_type')
            ->where('service_id', $service->id)
            ->where('service_type_id', $st->id)
            ->exists();
            
        if (!$exists) {
            \DB::table('service_service_type')->insert([
                'service_id' => $service->id,
                'service_type_id' => $st->id,
                'name' => current(explode(' (', $st->name)), // Provide a name
                'price' => $st->price ?? 0,
                'fixed' => $st->fixed ?? 0,
                'minute' => $st->minute ?? 0,
                'hour' => $st->hour ?? 0,
                'distance' => $st->distance ?? 0,
                'calculator' => $st->calculator ?? 'MIN',
                'description' => $st->description ?? '',
                'status' => $st->status ?? 1,
                'ambulance' => $st->ambulance ?? 0,
                'rental_amount' => $st->rental_amount ?? 0,
                'outstation_price' => $st->outstation_price ?? 0,
            ]);
            echo "Attached " . $st->name . " to " . $service->name . "\n";
        } else {
            echo $st->name . " is already attached to " . $service->name . "\n";
        }
    }
}
