<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Provider;
use App\SubscriptionPlan;

$provider = Provider::find(21);
$provider->load(['service.service_type.services']);

$serviceId = 1;
if ($provider->service && $provider->service->service_type && $provider->service->service_type->services->count() > 0) {
    $serviceId = $provider->service->service_type->services->first()->id;
}

$plans = SubscriptionPlan::where('status', 'active')->where('service_id', $serviceId)->get();

echo "Plans count: " . $plans->count() . "\n";
echo "Plans json: " . $plans->toJson() . "\n";
