<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$latitude = 5.3484;
$longitude = -4.0045;
$distance = 10;
$service_type_id = 1;
$requestedServiceCategoryId = 1;

$Providers = App\Provider::with(['service', 'subscriptionPlan'])
    ->select(Illuminate\Support\Facades\DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"), 'id', 'eco_wallet_balance', 'service_type_id', 'subscription_plan_id', 'status', 'is_smart_mode')
    ->where('status', 'approved')
    ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
    ->whereHas('service', function ($query) use ($service_type_id) {
        $query->where('status', 'active');
        $query->where('service_type_id', $service_type_id);
    })
    ->where(function ($query) use ($requestedServiceCategoryId) {
        $query->whereHas('selectedServices', function ($q) use ($requestedServiceCategoryId) {
            $q->where('service_id', $requestedServiceCategoryId)->where('is_active', true);
        })->orWhereDoesntHave('selectedServices');
    })
    ->orderBy('distance', 'asc')
    ->take(50)
    ->get();

echo 'Found providers count: ' . $Providers->count() . "\n";
foreach ($Providers as $p) {
    echo "Provider ID: {$p->id}, Distance: {$p->distance}, Service: " . ($p->service ? $p->service->service_type_id : 'null') . "\n";
}

$estimatedPrice = 20 + (5.2 * 10);
$commissionPercentage = 15;
$Providers = $Providers->filter(function ($provider) use ($estimatedPrice, $commissionPercentage) {
    return $provider->canAffordCommission($estimatedPrice, $commissionPercentage);
});
echo 'After canAffordCommission count: ' . $Providers->count() . "\n";
