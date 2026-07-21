<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

$mappings = [
    'UTB' => 'UTB',
    'SBTA' => 'SBTA',
    'CTE' => 'CTE',
    'STIF' => 'STIF'
];

foreach ($mappings as $compName => $serviceName) {
    echo "Linking Company '$compName' to Service '$serviceName'...\n";
    $company = App\InterurbanCompany::where('name', $compName)->first();
    $service = App\ServiceType::where('name', $serviceName)->first();

    if ($company && $service) {
        $service->interurban_company_id = $company->id;
        $service->save();
        echo "✅ Linked (Company ID: {$company->id}, Service ID: {$service->id})\n";
    } else {
        echo "❌ Mapping failed for $compName. Company: " . ($company ? 'Found' : 'MISSING') . ", Service: " . ($service ? 'Found' : 'MISSING') . "\n";
    }
}

echo "\nDone linking.\n";
