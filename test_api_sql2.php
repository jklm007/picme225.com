<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = \App\Service::with([
    'serviceTypes' => function ($query) {
        $query->where(function($q) {
            $q->whereJsonContains('service_types.allowed_variants', 'prive')
              ->orWhere('service_types.allowed_variants', 'LIKE', '%prive%')
              ->orWhereNull('service_types.allowed_variants')
              ->orWhere('service_types.allowed_variants', '[]')
              ->orWhere('service_types.allowed_variants', '');
        });
    }
])->where('name', 'Partage')->first();

echo "Partage service types: " . implode(", ", $service->serviceTypes->pluck('name')->toArray()) . "\n";
