<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\ServiceType;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

function attachServiceTypeToService($serviceTypeName, $serviceId, $serviceTypeIdOverride = null) {
    if ($serviceTypeIdOverride) {
        $serviceType = ServiceType::find($serviceTypeIdOverride);
    } else {
        $serviceType = ServiceType::where('name', $serviceTypeName)->first();
    }
    
    if ($serviceType) {
        $exists = DB::table('service_service_type')
            ->where('service_id', $serviceId)
            ->where('service_type_id', $serviceType->id)
            ->exists();
            
        if (!$exists) {
            DB::table('service_service_type')->insert([
                'service_id' => $serviceId,
                'service_type_id' => $serviceType->id,
                'name' => $serviceType->name, // Added 'name'
                'fixed' => $serviceType->fixed,
                'price' => $serviceType->price,
                'minute' => $serviceType->minute,
                'hour' => $serviceType->hour,
                'distance' => $serviceType->distance,
                'calculator' => $serviceType->calculator,
                'status' => 1
            ]);
            echo "Attached '{$serviceType->name}' (ID: {$serviceType->id}) to Service ID $serviceId.\n";
        } else {
            echo "'{$serviceType->name}' (ID: {$serviceType->id}) is already attached to Service ID $serviceId.\n";
        }
    } else {
        echo "Error: ServiceType '$serviceTypeName' not found!\n";
    }
}

// 1. Attach Location services to Location (id 3)
attachServiceTypeToService('SUV', 3, 8); // Explicitly use ID 8 for SUV (Rental)
attachServiceTypeToService('Berline', 3);

// 2. Attach Voyage services to Voyage (id 4)
attachServiceTypeToService('Berline Voyage', 4);
attachServiceTypeToService('SUV Voyage', 4);
attachServiceTypeToService('MiniBus Voyage', 4);

// 3. Attach Urgence services to Urgence (id 5)
attachServiceTypeToService('Ambulance', 5);
attachServiceTypeToService('Dépanneuse', 5);

// 4. Attach Livraison services to Livraison (id 2)
attachServiceTypeToService('Moto', 2);
attachServiceTypeToService('Cargo', 2);

echo "Done attaching service types to categories!\n";
