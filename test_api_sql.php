<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function testRealLogicSQL($searchName, $variant) {
    $skipVariantFilter = in_array($searchName, ['Location', 'Urgence']);
    $typeFilter = match ($searchName) {
        'Location'  => 'rental',
        'Livraison' => 'livraison',
        default     => null,
    };

    $query = \App\ServiceType::whereHas('service', function($q) use ($searchName) {
        $q->where('name', $searchName);
    });

    if ($typeFilter) {
        $query->where('type', $typeFilter);
    }

    if ($variant && !$skipVariantFilter) {
        $query->where(function($q) use ($variant) {
            $q->whereJsonContains('allowed_variants', $variant)
              ->orWhere('allowed_variants', 'LIKE', '%' . $variant . '%')
              ->orWhereNull('allowed_variants')
              ->orWhere('allowed_variants', '[]')
              ->orWhere('allowed_variants', '');
        });
    }

    echo $query->toSql() . "\n";
    echo json_encode($query->getBindings()) . "\n";
    return $query->pluck('name')->toArray();
}

echo "Partage (variant=prive): " . implode(", ", testRealLogicSQL('Partage', 'prive')) . "\n";
