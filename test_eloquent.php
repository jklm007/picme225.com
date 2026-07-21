<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$variant = 'partage';
$service = App\Service::with([
    'serviceTypes' => function ($query) use ($variant) {
        $query->where(function($q) use ($variant) {
            $q->whereJsonContains('service_types.allowed_variants', $variant)
              ->orWhere('service_types.allowed_variants', 'LIKE', '%' . $variant . '%')
              ->orWhereNull('service_types.allowed_variants');
        });
    }
])->where('name', 'Voyage')->first();

if ($service) {
    echo "Found service Voyage. Service Types:\n";
    foreach ($service->serviceTypes as $st) {
        echo "- ID: {$st->id}, Name: {$st->name}, Allowed: " . json_encode($st->allowed_variants) . "\n";
    }
} else {
    echo "Not found.\n";
}
