<?php require __DIR__.'/vendor/autoload.php'; $app = require_once __DIR__.'/bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 

// 1. Update existing rentals (Berline, SUV) to be 'avec_chauffeur'
App\Models\ServiceType::where('type', 'rental')->update(['allowed_variants' => json_encode(['avec_chauffeur'])]);

// 2. Create new ServiceTypes for 'sans_chauffeur' based on the existing ones
$existing = App\Models\ServiceType::where('type', 'rental')->get();
foreach($existing as $service) {
    // Avoid creating duplicates if we run this multiple times
    if(strpos($service->name, 'Sans Chauffeur') !== false) continue;
    
    $newName = $service->name . ' (Sans Chauffeur)';
    $exists = App\Models\ServiceType::where('name', $newName)->first();
    if(!$exists) {
        $newService = $service->replicate();
        $newService->name = $newName;
        $newService->allowed_variants = ['sans_chauffeur'];
        $newService->save();
        echo 'Created: ' . $newName . '\n';
    }
}

echo 'Update complete.\n';

