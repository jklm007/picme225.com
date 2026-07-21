<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$distanceRadius = env('USER_SEARCH_RADIUS', 50); // Usually 10, 50, etc.
$latitude = 5.3484;
$longitude = -4.0045;

$providers = App\Provider::with('service')
    ->select(Illuminate\Support\Facades\DB::raw("*, ( 6371 * acos( cos( radians($latitude) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * sin( radians( latitude ) ) ) ) AS distance"))
    ->having('distance', '<=', $distanceRadius)
    ->orderBy('distance', 'asc')
    ->get();

echo "Found providers within {$distanceRadius}km: " . $providers->count() . "\n";
foreach ($providers as $p) {
    if (!$p->service)
        continue;
    echo "Provider ID: {$p->id}, Distance: {$p->distance}, Service: {$p->service->service_type_id}, Status: {$p->status}, Availability: " . ($p->service->status ?? 'none') . ", ProviderStatus: " . ($p->status) . "\n";
}
