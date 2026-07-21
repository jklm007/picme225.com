<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
$controller = new \App\Http\Controllers\UserApiController();
// We'll call the logic directly or just mock the request to getServiceTypes
// Let's copy the logic roughly
function testCategory($name, $variant) {
    $skipVariantFilter = in_array($name, ['Location', 'Urgence']);
    $typeFilter = match ($name) {
        'Location'  => 'rental',
        'Livraison' => 'livraison',
        default     => null,
    };

    $query = \App\ServiceType::whereHas('service', function($q) use ($name) {
        $q->where('name', $name);
    });

    if ($typeFilter) {
        $query->where('type', $typeFilter);
    }
    
    if ($variant && !$skipVariantFilter) {
        $query->where(function($q) use ($variant) {
            $q->whereJsonContains('allowed_variants', $variant)
              ->orWhere('allowed_variants', 'LIKE', '%' . $variant . '%')
              ->orWhereNull('allowed_variants');
        });
    }
    
    return $query->get(['id', 'name', 'type', 'allowed_variants'])->toArray();
}

echo "Location (variant=prive):\n";
print_r(testCategory('Location', 'prive'));

echo "Urgence (variant=prive):\n";
print_r(testCategory('Urgence', 'prive'));

echo "Partage (variant=prive):\n";
print_r(testCategory('Partage', 'prive'));

