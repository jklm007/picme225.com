<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;
use App\ProviderService;
use App\ServiceType;
use App\Service;
use App\SubscriptionPlan;
use App\Http\Controllers\ProviderResources\ProfileController;
use App\Http\Controllers\UserApiController;
use App\Services\DispatchEngine\MatchingService;
use App\KmHour;
use Illuminate\Http\Request;

echo "=================================================================\n";
echo "       RUNNING INTEGRATION TESTS FOR RENTAL & CATEGORY LIMITS    \n";
echo "=================================================================\n\n";

// Helper to print assert status
function assert_test($label, $assertion, $data = null) {
    if ($assertion) {
        echo "✅ SUCCESS: {$label}\n";
    } else {
        echo "❌ FAILED: {$label}\n";
        if ($data !== null) {
            echo "   Response Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
        }
        exit(1);
    }
}

// -----------------------------------------------------------------
// TEST 1: Subscription Quota & Eligibility check in ProfileController
// -----------------------------------------------------------------
echo "--- TEST 1: CATEGORY SELECTION QUOTAS & ELIGIBILITY ---\n";

// Setup plans
$freePlan = SubscriptionPlan::updateOrCreate(
    ['name' => 'FREE_TEST'],
    [
        'max_categories' => 1,
        'price' => 0,
        'commission_value' => 25,
        'commission_type' => 'percentage',
        'period' => 'DAILY',
        'status' => 'active',
    ]
);

$proPlan = SubscriptionPlan::updateOrCreate(
    ['name' => 'PRO_TEST'],
    [
        'max_categories' => 3,
        'price' => 10000,
        'commission_value' => 15,
        'commission_type' => 'percentage',
        'period' => 'DAILY',
        'status' => 'active',
    ]
);

// Setup service types (use existing Berline Rental ID 7)
$st = ServiceType::findOrFail(7);
$st->type = 'rental';
$st->calculator = 'HOUR';
$st->save();

// Setup services (categories)
$cat1 = Service::updateOrCreate(['name' => 'Taxi'], ['status' => 'active']);
$cat2 = Service::updateOrCreate(['name' => 'Livraison'], ['status' => 'active']);
$cat3 = Service::updateOrCreate(['name' => 'Location'], ['status' => 'active']);
$cat4 = Service::updateOrCreate(['name' => 'Partage'], ['status' => 'active']);

// Link Taxi, Livraison, Location, and Partage to Test SUV in pivot
DB::table('service_service_type')->where('service_type_id', $st->id)->delete();
DB::table('service_service_type')->insert([
    [
        'service_type_id' => $st->id,
        'service_id' => $cat1->id,
        'name' => 'Taxi',
        'provider_name' => 'Driver Taxi',
        'fixed' => 200,
        'price' => 100,
        'minute' => 0,
        'distance' => 1,
        'calculator' => 'DISTANCE',
        'capacity' => 4,
    ],
    [
        'service_type_id' => $st->id,
        'service_id' => $cat2->id,
        'name' => 'Livraison',
        'provider_name' => 'Driver Livraison',
        'fixed' => 500,
        'price' => 150,
        'minute' => 0,
        'distance' => 1,
        'calculator' => 'DISTANCE',
        'capacity' => 4,
    ],
    [
        'service_type_id' => $st->id,
        'service_id' => $cat3->id,
        'name' => 'Location',
        'provider_name' => 'Driver Location',
        'fixed' => 5000,
        'price' => 0,
        'minute' => 0,
        'distance' => 1,
        'calculator' => 'HOUR',
        'capacity' => 4,
    ],
    [
        'service_type_id' => $st->id,
        'service_id' => $cat4->id,
        'name' => 'Partage',
        'provider_name' => 'Driver Partage',
        'fixed' => 150,
        'price' => 50,
        'minute' => 0,
        'distance' => 1,
        'calculator' => 'DISTANCE',
        'capacity' => 4,
    ],
]);

// Setup provider
$provider = Provider::updateOrCreate(
    ['email' => 'driver_test_quota@picme.com'],
    [
        'first_name' => 'Test',
        'last_name' => 'Quota',
        'mobile' => '+22500000000',
        'password' => bcrypt('password'),
        'subscription_plan_id' => $freePlan->id,
        'status' => 'approved',
        'commune' => 'Cocody',
        'available' => 1,
        'latitude' => 5.3484,
        'longitude' => -4.0244,
        'eco_wallet_balance' => 100.0,
    ]
);

ProviderService::updateOrCreate(
    ['provider_id' => $provider->id],
    [
        'service_type_id' => $st->id,
        'status' => 'active',
        'service_model' => 'SUV Test',
        'service_number' => 'SUV-123-AA',
    ]
);

\Auth::login($provider);
\Auth::guard('provider')->setUser($provider);
\Auth::guard('providerapi')->setUser($provider);

$profileController = new ProfileController();

// A. FREE driver selects 2 eligible categories => should fail (limit is 1)
$req1 = Request::create('/api/provider/services', 'POST', [
    'services' => [$cat1->id, $cat2->id]
]);
$res1 = $profileController->update_service_selection($req1);
$data1 = json_decode(json_encode($res1->getData()), true);
assert_test("Free driver blocked from selecting 2 categories", isset($data1['error']) && str_contains($data1['error'], 'Votre abonnement'), $data1);

// B. FREE driver selects 1 eligible category => should pass
$req2 = Request::create('/api/provider/services', 'POST', [
    'services' => [$cat1->id]
]);
$res2 = $profileController->update_service_selection($req2);
$data2 = json_decode(json_encode($res2->getData()), true);
assert_test("Free driver can select 1 eligible category", isset($data2['message']) && str_contains(strtolower($data2['message']), 'selection'), $data2);

// C. PRO driver selects 3 eligible categories => should pass
$provider->subscription_plan_id = $proPlan->id;
$provider->save();
$provider = $provider->fresh();
\Auth::guard('providerapi')->setUser($provider);

$req3 = Request::create('/api/provider/services', 'POST', [
    'services' => [$cat1->id, $cat2->id, $cat3->id]
]);
$res3 = $profileController->update_service_selection($req3);
$data3 = json_decode(json_encode($res3->getData()), true);
assert_test("PRO driver can select 3 eligible categories", isset($data3['message']) && str_contains(strtolower($data3['message']), 'selection'), $data3);

// D. PRO driver selects 4 eligible categories => should fail (limit is 3)
$req4 = Request::create('/api/provider/services', 'POST', [
    'services' => [$cat1->id, $cat2->id, $cat3->id, $cat4->id]
]);
$res4 = $profileController->update_service_selection($req4);
$data4 = json_decode(json_encode($res4->getData()), true);
assert_test("PRO driver blocked from selecting 4 categories", isset($data4['error']) && str_contains($data4['error'], 'Votre abonnement'), $data4);

// E. PRO driver selects non-eligible category (Partage - not in pivot table) => should fail
// First remove Partage (cat4) from pivot so it becomes non-eligible
DB::table('service_service_type')->where('service_type_id', $st->id)->where('service_id', $cat4->id)->delete();

$req5 = Request::create('/api/provider/services', 'POST', [
    'services' => [$cat4->id]
]);
$res5 = $profileController->update_service_selection($req5);
$data5 = json_decode(json_encode($res5->getData()), true);
assert_test("Driver blocked from selecting non-eligible category (Partage)", isset($data5['error']) && (str_contains($data5['error'], 'autorisé') || str_contains($data5['error'], 'valid service')), $data5);

// F. Selection of Location saves preference
$req6 = Request::create('/api/provider/services', 'POST', [
    'services' => [$cat3->id],
    'rental_driver_preference' => 'WITHOUT_DRIVER'
]);
$res6 = $profileController->update_service_selection($req6);
$data6 = json_decode(json_encode($res6->getData()), true);
assert_test("Selecting Location accepts and saves rental_driver_preference", isset($data6['message']), $data6);

$prefSaved = DB::table('provider_services')->where('provider_id', $provider->id)->value('rental_driver_preference');
assert_test("rental_driver_preference saved correctly as WITHOUT_DRIVER", $prefSaved === 'WITHOUT_DRIVER');

echo "\n";

// -----------------------------------------------------------------
// TEST 2: Duration-Based Self-Drive Enforcements
// -----------------------------------------------------------------
echo "--- TEST 2: RENTAL DURATION ENFORCEMENT ---\n";

// Update driver preference to BOTH to allow matching both with/without driver requests
DB::table('provider_services')
    ->where('provider_id', $provider->id)
    ->update(['rental_driver_preference' => 'BOTH']);

// Setup User
$user = User::updateOrCreate(
    ['email' => 'client_test_rental@picme.com'],
    [
        'first_name' => 'Jean',
        'last_name' => 'Rental',
        'mobile' => '+22500000001',
        'password' => bcrypt('password'),
        'payment_mode' => 'CASH',
        'wallet_balance' => 50000,
        'kyc_status' => 'APPROVED',
    ]
);

\Auth::login($user);

// Create KmHour hourly package (< 24h)
$kmHour10h = KmHour::where('hour', 10)->first();
if (!$kmHour10h) {
    $kmHour10h = new KmHour();
    $kmHour10h->hour = 10;
    $kmHour10h->kilometer = 100;
    $kmHour10h->save();
}
DB::table('km_hour_service_type_prices')->updateOrInsert(
    ['km_hour_id' => $kmHour10h->id, 'service_type_id' => $st->id],
    ['price' => 15000]
);

// Create KmHour daily package (>= 24h)
$kmHour24h = KmHour::where('hour', 24)->first();
if (!$kmHour24h) {
    $kmHour24h = new KmHour();
    $kmHour24h->hour = 24;
    $kmHour24h->kilometer = 250;
    $kmHour24h->save();
}
DB::table('km_hour_service_type_prices')->updateOrInsert(
    ['km_hour_id' => $kmHour24h->id, 'service_type_id' => $st->id],
    ['price' => 35000]
);

// Link to Service
$st->services()->syncWithoutDetaching([$cat3->id]);

// A. Send request with hourly package (10h), with_driver = false => should force with_driver = true (1)
$reqRequest1 = Request::create('/api/user/send/request', 'POST', [
    's_latitude' => 5.3484,
    's_longitude' => -4.0244,
    'd_latitude' => 5.3584,
    'd_longitude' => -4.0144,
    'service_type' => $st->id,
    'payment_mode' => 'CASH',
    'package_id' => $kmHour10h->id,
    'with_driver' => 'false',
]);
$reqRequest1->headers->set('X-Requested-With', 'XMLHttpRequest');
$reqRequest1->headers->set('Accept', 'application/json');

$userApiController = new UserApiController();
$resRequest1 = $userApiController->send_request($reqRequest1);
$dataRequest1 = json_decode(json_encode($resRequest1->getData()), true);

assert_test("Request created for 10h package", isset($dataRequest1['request_id']), $dataRequest1);
$dbRequest1 = \App\UserRequests::find($dataRequest1['request_id']);
assert_test("10h package (<24h) forces with_driver = 1", (int)$dbRequest1->with_driver === 1);

// Clean up
if ($dbRequest1) {
    $dbRequest1->delete();
}

// B. Send request with daily package (24h), with_driver = false => should respect with_driver = false (0)
$reqRequest2 = Request::create('/api/user/send/request', 'POST', [
    's_latitude' => 5.3484,
    's_longitude' => -4.0244,
    'd_latitude' => 5.3584,
    'd_longitude' => -4.0144,
    'service_type' => $st->id,
    'payment_mode' => 'CASH',
    'package_id' => $kmHour24h->id,
    'with_driver' => 'false',
]);
$reqRequest2->headers->set('X-Requested-With', 'XMLHttpRequest');
$reqRequest2->headers->set('Accept', 'application/json');

$resRequest2 = $userApiController->send_request($reqRequest2);
$dataRequest2 = json_decode(json_encode($resRequest2->getData()), true);

assert_test("Request created for 24h package", isset($dataRequest2['request_id']), $dataRequest2);
$dbRequest2 = \App\UserRequests::find($dataRequest2['request_id']);
assert_test("24h package (>=24h) respects with_driver = 0 when allow_without_driver is true", (int)$dbRequest2->with_driver === 0);

// Clean up
if ($dbRequest2) {
    $dbRequest2->delete();
}

// C. Set allow_without_driver = false on ServiceType, and send request with daily package (24h) => should force with_driver = true (1)
$st->allow_without_driver = false;
$st->save();

$reqRequest3 = Request::create('/api/user/send/request', 'POST', [
    's_latitude' => 5.3484,
    's_longitude' => -4.0244,
    'd_latitude' => 5.3584,
    'd_longitude' => -4.0144,
    'service_type' => $st->id,
    'payment_mode' => 'CASH',
    'package_id' => $kmHour24h->id,
    'with_driver' => 'false',
]);
$reqRequest3->headers->set('X-Requested-With', 'XMLHttpRequest');
$reqRequest3->headers->set('Accept', 'application/json');

$resRequest3 = $userApiController->send_request($reqRequest3);
$dataRequest3 = json_decode(json_encode($resRequest3->getData()), true);

assert_test("Request created for 24h package with allow_without_driver = false", isset($dataRequest3['request_id']), $dataRequest3);
$dbRequest3 = \App\UserRequests::find($dataRequest3['request_id']);
assert_test("24h package forces with_driver = 1 when allow_without_driver is false", (int)$dbRequest3->with_driver === 1);

// Restore service type configuration
$st->allow_without_driver = true;
$st->save();

// Clean up
if ($dbRequest3) {
    $dbRequest3->delete();
}

echo "\n";

// -----------------------------------------------------------------
// TEST 3: Dispatch Preference Matching
// -----------------------------------------------------------------
echo "--- TEST 3: DISPATCH MATCHING BY PREFERENCE ---\n";

// Set up Driver A: WITH_DRIVER
$driverA = Provider::updateOrCreate(
    ['email' => 'driver_with@picme.com'],
    [
        'first_name' => 'Driver',
        'last_name' => 'With',
        'mobile' => '+22500000002',
        'password' => bcrypt('password'),
        'status' => 'approved',
        'latitude' => 5.3484,
        'longitude' => -4.0244,
        'eco_wallet_balance' => 10.0,
        'commune' => 'Cocody',
        'available' => 1,
    ]
);
ProviderService::updateOrCreate(
    ['provider_id' => $driverA->id],
    [
        'service_type_id' => $st->id,
        'status' => 'active',
        'rental_driver_preference' => 'WITH_DRIVER',
    ]
);

// Set up Driver B: WITHOUT_DRIVER
$driverB = Provider::updateOrCreate(
    ['email' => 'driver_without@picme.com'],
    [
        'first_name' => 'Driver',
        'last_name' => 'Without',
        'mobile' => '+22500000003',
        'password' => bcrypt('password'),
        'status' => 'approved',
        'latitude' => 5.3484,
        'longitude' => -4.0244,
        'eco_wallet_balance' => 10.0,
        'commune' => 'Cocody',
        'available' => 1,
    ]
);
ProviderService::updateOrCreate(
    ['provider_id' => $driverB->id],
    [
        'service_type_id' => $st->id,
        'status' => 'active',
        'rental_driver_preference' => 'WITHOUT_DRIVER',
    ]
);

// Instantiate MatchingService
$geoService = app(\App\Services\DispatchEngine\GeoService::class);
$routingService = app(\App\Services\DispatchEngine\RoutingService::class);
$scoreService = app(\App\Services\DispatchEngine\ScoreService::class);
$matchingService = new MatchingService($geoService, $scoreService, $routingService);

// Case 1: Search with with_driver = true
$ctxWith = [
    's_lat' => 5.3484,
    's_lng' => -4.0244,
    'service_type_id' => $st->id,
    'search_radius_km' => 10,
    'estimated_price' => 5000,
    'commission_rate' => 15,
    'with_driver' => true,
];
$matchedWith = $matchingService->findBestDrivers($ctxWith);
$driverIdsWith = $matchedWith->pluck('id')->toArray();

assert_test("Dispatch with_driver=true matches Driver A (WITH_DRIVER)", in_array($driverA->id, $driverIdsWith));
assert_test("Dispatch with_driver=true excludes Driver B (WITHOUT_DRIVER)", !in_array($driverB->id, $driverIdsWith));

// Case 2: Search with with_driver = false
$ctxWithout = [
    's_lat' => 5.3484,
    's_lng' => -4.0244,
    'service_type_id' => $st->id,
    'search_radius_km' => 10,
    'estimated_price' => 5000,
    'commission_rate' => 15,
    'with_driver' => false,
];
$matchedWithout = $matchingService->findBestDrivers($ctxWithout);
$driverIdsWithout = $matchedWithout->pluck('id')->toArray();

assert_test("Dispatch with_driver=false matches Driver B (WITHOUT_DRIVER)", in_array($driverB->id, $driverIdsWithout));
assert_test("Dispatch with_driver=false excludes Driver A (WITH_DRIVER)", !in_array($driverA->id, $driverIdsWithout));

echo "\n=================================================================\n";
echo "                ALL INTEGRATION TESTS PASSED!                     \n";
echo "=================================================================\n";
