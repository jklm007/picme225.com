<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function testRealLogic($searchName, $variant) {
    $skipVariantFilter = in_array($searchName, ['Location', 'Urgence']);
    $typeFilter = match ($searchName) {
        'Location'  => 'rental',
        'Livraison' => 'livraison',
        default     => null,
    };

    $service = \App\Service::with([
        'serviceTypes' => function ($query) use ($variant, $skipVariantFilter, $typeFilter) {
            if ($typeFilter) {
                $query->where('service_types.type', $typeFilter);
            }

            if ($variant && !$skipVariantFilter) {
                $query->where(function($q) use ($variant) {
                    $q->whereJsonContains('service_types.allowed_variants', $variant)
                      ->orWhere('service_types.allowed_variants', 'LIKE', '%' . $variant . '%')
                      ->orWhereNull('service_types.allowed_variants');
                });
            }
        }
    ])->where('name', $searchName)->first();

    if ($service) {
        return $service->serviceTypes->pluck('name')->toArray();
    }
    return [];
}

echo "Location (variant=prive): " . implode(", ", testRealLogic('Location', 'prive')) . "\n";
echo "Urgence (variant=prive): " . implode(", ", testRealLogic('Urgence', 'prive')) . "\n";
echo "Partage (variant=prive): " . implode(", ", testRealLogic('Partage', 'prive')) . "\n";

