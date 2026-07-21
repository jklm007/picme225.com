<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;
use App\Provider;
use App\ServiceType;
use App\UserRequests;
use Illuminate\Http\Request;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\ProviderResources\TripController;
use Illuminate\Support\Facades\Auth;

echo "===============================================\n";
echo "       PICME PRO - END-TO-END RIDE TESTS       \n";
echo "===============================================\n\n";

// Auth as first user
$user = User::first();
$user->kyc_status = 'APPROVED';
$user->save();
Auth::login($user);
echo "Logged in as User: {$user->email}\n";

// Fund drivers so they pass commission checks
Provider::query()->update(['eco_wallet_balance' => 50000]);

// Configure Taxi Vtc drivers for partage / arret_pdp (premium variants)
$taxiVtcId = ServiceType::where('name', 'like', '%Taxi Vtc%')->value('id') ?? 1;
$expiresAt = \Carbon\Carbon::now()->addYear();
foreach (\App\ProviderService::where('service_type_id', $taxiVtcId)->where('status', 'active')->pluck('provider_id')->unique() as $pid) {
    $p = Provider::find($pid);
    if (!$p || $p->status !== 'approved') continue;
    $p->opt_private_ride = 1;
    $p->opt_share_ride = 1;
    $p->opt_arret_ride = 1;
    $p->opt_multi_stop = 1;
    if (empty($p->subscription_expires_at) || \Carbon\Carbon::parse($p->subscription_expires_at)->isPast()) {
        $p->subscription_expires_at = $expiresAt;
    }
    if (empty($p->latitude) || empty($p->longitude)) {
        $p->latitude = 5.345;
        $p->longitude = -4.024;
    }
    $p->save();
}

$serviceTypes = ServiceType::all();

$userController = new UserApiController();
$tripController = new TripController();

foreach ($serviceTypes as $st) {
    echo "\n-----------------------------------------------\n";
    echo "Testing Service: {$st->name} (Type: {$st->type})\n";
    
    // Determine variations to test
    $variants = [];
    if (!empty($st->allowed_variants)) {
        if (is_string($st->allowed_variants)) {
            $variants = json_decode($st->allowed_variants, true) ?? [];
        } else {
            $variants = $st->allowed_variants;
        }
    } else {
        $variants = ['prive'];
    }

    if (empty($variants)) $variants = ['prive'];

    foreach ($variants as $variant) {
        echo " -> Testing Variant: $variant\n";
        
        try {
            // 1. Create Request
            $reqData = [
                's_latitude' => 5.3484,
                's_longitude' => -4.0305,
                'd_latitude' => 5.3500,
                'd_longitude' => -4.0000,
                'service_type' => $st->id,
                'distance' => 5,
                'payment_mode' => 'CASH',
                'ride_variant' => $variant,
                'type' => $st->type,
            ];
            
            if ($st->type == 'livraison') {
                $reqData['sender_name'] = 'Test Sender';
                $reqData['sender_phone'] = '0101010101';
                $reqData['receiver_name'] = 'Test Receiver';
                $reqData['receiver_phone'] = '0202020202';
            }
            if ($st->type == 'rental') {
                $reqData['rental_package'] = 1; // 1 hour package
            }
            if ($variant == 'partage' || $variant == 'arret_pdp') {
                $reqData['booked'] = 1;
            }

            $request = new Request($reqData);
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            $request->setUserResolver(function() use ($user) { return $user; });

            // Clear previous open requests for this user
            UserRequests::where('user_id', $user->id)->delete();

            // SEND REQUEST
            $response = $userController->send_request($request);
            $resContent = json_decode($response->getContent(), true);

            if (!$resContent || !isset($resContent['request_id'])) {
                echo "   [FAIL] No request_id in response. Raw Res: " . $response->getContent() . "\n";
                continue;
            }

            $requestId = $resContent['request_id'];
            echo "   [OK] Request Created. ID: $requestId\n";

            // 1b. Cancel while SEARCHING (user cancel flow)
            $cancelReq = new Request([
                'request_id' => $requestId,
                'cancel_reason' => 'Test annulation',
            ]);
            $cancelReq->headers->set('Accept', 'application/json');
            $cancelReq->headers->set('X-Requested-With', 'XMLHttpRequest');
            $cancelReq->setUserResolver(function () use ($user) { return $user; });
            Auth::login($user);
            $cancelRes = $userController->cancel_request($cancelReq);
            $cancelBody = json_decode($cancelRes->getContent(), true);
            if ($cancelRes->getStatusCode() !== 200 || empty($cancelBody['message'])) {
                echo "   [FAIL] Cancel failed. HTTP {$cancelRes->getStatusCode()} | " . $cancelRes->getContent() . "\n";
                continue;
            }
            $trip = UserRequests::find($requestId);
            if (!$trip || $trip->status != 'CANCELLED') {
                echo "   [FAIL] Trip not CANCELLED after cancel. Status: " . ($trip->status ?? 'null') . "\n";
                continue;
            }
            echo "   [OK] Cancel while SEARCHING succeeded.\n";

            // Recreate for provider accept flow
            UserRequests::where('user_id', $user->id)->delete();
            $response = $userController->send_request($request);
            $resContent = json_decode($response->getContent(), true);
            if (!$resContent || !isset($resContent['request_id'])) {
                echo "   [FAIL] Recreate after cancel failed.\n";
                continue;
            }
            $requestId = $resContent['request_id'];
            echo "   [OK] Request recreated. ID: $requestId\n";

            $trip = UserRequests::find($requestId);
            if (!$trip || $trip->status != 'SEARCHING') {
                echo "   [FAIL] Trip not in SEARCHING status or not found.\n";
                continue;
            }

            // 2. Find Assigned Provider
            $providerId = $trip->provider_id;
            if ($providerId == 0) {
                // Find via RequestFilter
                $filter = \App\RequestFilter::where('request_id', $requestId)->first();
                if ($filter) {
                    $providerId = $filter->provider_id;
                }
            }

            if (!$providerId) {
                echo "   [WARN] No provider assigned. Make sure provider is ONLINE and AVAILABLE.\n";
                // Let's force assign a provider for this test
                $provider = Provider::whereHas('service', function($q) use ($st) {
                    $q->where('service_type_id', $st->id);
                })->first();
                if ($provider) {
                    $providerId = $provider->id;
                    $trip->provider_id = $providerId;
                    $trip->current_provider_id = $providerId;
                    $trip->save();
                    echo "   [INFO] Force assigned Provider ID: $providerId\n";
                } else {
                    echo "   [FAIL] No provider exists for this service type.\n";
                    continue;
                }
            }

            // Auth as Provider
            $provider = Provider::find($providerId);
            Auth::guard('provider')->login($provider);

            // 3. Provider ACCEPTS
            $acceptReq = new Request(['id' => $requestId]);
            $acceptReq->setUserResolver(function() use ($provider) { return $provider; });
            $acceptRes = $tripController->accept($acceptReq, $requestId);
            $trip->refresh();
            if (!in_array($trip->status, ['ACCEPTED', 'STARTED'])) {
                echo "   [FAIL] Accept failed. Status: {$trip->status}\n";
                continue;
            }
            echo "   [OK] Provider Accepted. Status: {$trip->status}\n";

            // 4. Provider ARRIVED
            $updateReq = new Request(['status' => 'ARRIVED']);
            $updateReq->headers->set('Accept', 'application/json');
            $updateReq->headers->set('X-Requested-With', 'XMLHttpRequest');
            $updateReq->setUserResolver(function() use ($provider) { return $provider; });
            $tripController->update($updateReq, $requestId);
            $trip->refresh();
            if ($trip->status != 'ARRIVED') {
                echo "   [FAIL] Arrived failed. Status: {$trip->status}\n";
                continue;
            }
            echo "   [OK] Provider Arrived.\n";

            // 5. Provider PICKEDUP
            $updateReq = new Request(['status' => 'PICKEDUP', 'otp' => $trip->otp]);
            $updateReq->headers->set('Accept', 'application/json');
            $updateReq->headers->set('X-Requested-With', 'XMLHttpRequest');
            $updateReq->setUserResolver(function() use ($provider) { return $provider; });
            $tripController->update($updateReq, $requestId);
            $trip->refresh();
            if ($trip->status != 'PICKEDUP') {
                echo "   [FAIL] Pickedup failed. Status: {$trip->status}\n";
                continue;
            }
            echo "   [OK] Provider Picked Up.\n";

            // 6. Provider DROPPED
            $dropReqData = [
                'status' => 'DROPPED',
                'distance' => 5,
            ];
            if ($st->type == 'livraison') {
                $dropReqData['recipient_name'] = 'Test';
                $dropReqData['recipient_mobile'] = '0101010101';
                $dropReqData['otp'] = $trip->otp;
            }
            $updateReq = new Request($dropReqData);
            $updateReq->headers->set('Accept', 'application/json');
            $updateReq->headers->set('X-Requested-With', 'XMLHttpRequest');
            $updateReq->setUserResolver(function() use ($provider) { return $provider; });
            
            // Mock file upload if needed for delivery, but backend usually checks if file exists.
            // We might get an error if signature is required, but let's see.
            $dropRes = $tripController->update($updateReq, $requestId);
            $trip->refresh();
            if ($trip->status != 'DROPPED') {
                // Log what failed
                $content = $dropRes->getContent();
                echo "   [FAIL] Dropped failed. Status: {$trip->status} | Res: $content\n";
                continue;
            }
            echo "   [OK] Provider Dropped.\n";

            echo "   [SUCCESS] End-to-end flow complete for $variant!\n";

        } catch (\Illuminate\Validation\ValidationException $e) {
            echo "   [VALIDATION ERROR] " . json_encode($e->errors()) . "\n";
        } catch (\Exception $e) {
            echo "   [ERROR] " . $e->getMessage() . "\n";
        }
    }
}
echo "\n===============================================\n";
echo "                  TESTS FINISHED               \n";
echo "===============================================\n";

