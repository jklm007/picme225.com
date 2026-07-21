<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\ServiceType;
use App\Models\UserRequests;
use App\Models\RequestFilter;
use App\Models\KmHour;
use App\Models\KmHourServiceTypePrice;
use App\Models\ServiceTypeRental;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\ProviderResources\TripController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class SimulateTripsFlow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simulate:trip-flows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate full trip lifecycles (Request -> Accept -> Arrive -> Start -> Drop off -> Complete) for Taxi, Livraison, Location, and Partage.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("=================================================================");
        $this->info("             PICME TRIP LIFECYCLE SIMULATOR                      ");
        $this->info("=================================================================");

        // 1. Create or retrieve test User
        $user = User::updateOrCreate(
            ['email' => 'test_simulation_user@picme.com'],
            [
                'first_name' => 'Jean',
                'last_name' => 'Client',
                'mobile' => '+2250102030405',
                'password' => bcrypt('password'),
                'payment_mode' => 'CASH',
                'wallet_balance' => 100000,
                'kyc_status' => 'APPROVED',
            ]
        );
        $user->wallet_balance = 100000;
        $user->kyc_status = 'APPROVED';
        $user->save();
        $this->info("Client User: {$user->first_name} {$user->last_name} (ID: {$user->id})");

        // Set up the service configurations
        $this->setupRentalPackages();

        // 2. Categories to test
        $categories = [
            'taxi' => [
                'name' => 'Taxi Category',
                'service_type_id' => 1, // Taxi Vtc
                'request_params' => [
                    's_latitude' => 5.3484,
                    's_longitude' => -4.0244,
                    'd_latitude' => 5.3584,
                    'd_longitude' => -4.0144,
                    's_address' => 'Cocody St Jean',
                    'd_address' => 'Angre Petrole',
                    'service_type' => 1,
                    'payment_mode' => 'CASH',
                    'distance' => 3.5,
                ]
            ],
            'livraison' => [
                'name' => 'Livraison (Delivery) Category',
                'service_type_id' => 5, // Moto (livraison)
                'request_params' => [
                    's_latitude' => 5.3484,
                    's_longitude' => -4.0244,
                    'd_latitude' => 5.3584,
                    'd_longitude' => -4.0144,
                    's_address' => 'Cocody Center',
                    'd_address' => 'Adjamé Market',
                    'service_type' => 5,
                    'payment_mode' => 'CASH',
                    'distance' => 5.2,
                    'method' => 'delivery',
                    'sender_name' => 'Jean Sender',
                    'sender_phone' => '01010101',
                    'receiver_name' => 'Marc Receiver',
                    'receiver_phone' => '02020202',
                    'package_description' => 'Document urgent',
                ]
            ],
            'location' => [
                'name' => 'Location (Rental) Category',
                'service_type_id' => 7, // Berline (rental)
                'request_params' => [
                    's_latitude' => 5.3484,
                    's_longitude' => -4.0244,
                    'd_latitude' => 5.3484,
                    'd_longitude' => -4.0244,
                    's_address' => 'Hotel Ivoire Cocody',
                    'd_address' => 'Hotel Ivoire Cocody (Retour)',
                    'service_type' => 7,
                    'payment_mode' => 'CASH',
                    'distance' => 0.0,
                    'rental_package' => 1, // 1 Hour, 20 km
                ]
            ],
            'partage' => [
                'name' => 'Partage (Shared / PDP) Category',
                'service_type_id' => 15, // Woro-Woro (SHARED)
                'request_params' => [
                    's_latitude' => 5.337255,
                    's_longitude' => -4.003548,
                    'd_latitude' => 5.372508,
                    'd_longitude' => -3.930119,
                    's_address' => 'Pharmacie St Jean de Cocody',
                    'd_address' => 'Carrefour fin goudron',
                    'service_type' => 15,
                    'payment_mode' => 'CASH',
                    'ride_variant' => 'arret_pdp',
                    'pickup_stop_id' => 3, // Pharmacie St Jean de Cocody
                    'dropoff_stop_id' => 11, // Carrefour fin goudron
                ]
            ],
            'partage_hybride' => [
                'name' => 'Partage Hybride (Fixed Stop -> Free Destination) Category',
                'service_type_id' => 15, // Woro-Woro (SHARED)
                'request_params' => [
                    's_latitude' => 5.337255,
                    's_longitude' => -4.003548,
                    'd_latitude' => 5.3624, // Detour point near Angre
                    'd_longitude' => -4.0044,
                    's_address' => 'Pharmacie St Jean de Cocody',
                    'd_address' => 'Angre Villa Libre',
                    'service_type' => 15,
                    'payment_mode' => 'CASH',
                    'ride_variant' => 'arret_hybride',
                    'pickup_stop_id' => 3, // Pharmacie St Jean de Cocody
                ]
            ]
        ];

        foreach ($categories as $key => $cat) {
            $this->info("\n-----------------------------------------------------------------");
            $this->info(" RUNNING SIMULATION FOR: " . strtoupper($cat['name']));
            $this->info("-----------------------------------------------------------------");

            // Clean up any stale active requests for the user
            UserRequests::where('user_id', $user->id)->delete();
            RequestFilter::whereHas('request', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->delete();

            // Create provider for the service type
            $provider = $this->setupProvider($cat['service_type_id']);
            $this->info("Provider: {$provider->first_name} {$provider->last_name} (ID: {$provider->id}) | Wallet Balance: " . ($provider->eco_wallet_balance * 1000) . " CFA | eco_wallet_balance: {$provider->eco_wallet_balance} ECO");

            // Run the lifecycle
            $this->runLifecycle($user, $provider, $cat['request_params']);
        }

        $this->info("\n=================================================================");
        $this->info("           ALL SIMULATIONS COMPLETED SUCCESSFULLY!                ");
        $this->info("=================================================================");
    }

    /**
     * Configure KmHour rental package for Location Category
     */
    private function setupRentalPackages()
    {
        // Ensure KmHour ID 1 exists
        $kmHour = KmHour::firstOrCreate(
            ['id' => 1],
            [
                'hour' => 1,
                'kilometer' => 20
            ]
        );

        // Ensure ServiceType ID 7 is rental
        $st = ServiceType::find(7);
        if ($st) {
            $st->type = 'rental';
            $st->calculator = 'HOUR';
            $st->save();

            // Link in KmHourServiceTypePrice
            KmHourServiceTypePrice::updateOrCreate(
                ['km_hour_id' => 1, 'service_type_id' => 7],
                ['price' => 5000]
            );

            // Link in ServiceTypeRental
            ServiceTypeRental::updateOrCreate(
                ['service_type_id' => 7, 'km_hour_id' => 1],
                ['ren_price' => 5000]
            );

            $this->info("Rental Package linked for ServiceType ID 7 (Berline, hourly/km rate: 5000 CFA)");
        }
    }

    private function setupProvider($serviceTypeId)
    {
        $stName = ServiceType::find($serviceTypeId)->name ?? 'Unknown';
        $lat = ($serviceTypeId == 15) ? 5.337255 : 5.3484;
        $lng = ($serviceTypeId == 15) ? -4.003548 : -4.0244;
        
        $provider = Provider::updateOrCreate(
            ['email' => "simulation_driver_{$serviceTypeId}@picme.com"],
            [
                'first_name' => 'Chauffeur',
                'last_name' => $stName,
                'mobile' => '+2250203040' . str_pad($serviceTypeId, 2, '0', STR_PAD_LEFT),
                'password' => bcrypt('password'),
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => 'approved',
                'commune' => 'Cocody',
                'eco_wallet_balance' => 5.0, // At least 1 ECO (1000 CFA)
            ]
        );

        $provider->status = 'approved';
        $provider->available = true;
        $provider->latitude = $lat;
        $provider->longitude = $lng;
        $provider->eco_wallet_balance = 5.0; // reset
        $provider->save();

        ProviderService::updateOrCreate(
            ['provider_id' => $provider->id],
            [
                'service_type_id' => $serviceTypeId,
                'status' => 'active',
                'service_model' => 'Simulation Car',
                'service_number' => 'SIM-123-AA',
            ]
        );

        return $provider;
    }

    /**
     * Run the lifecycle state transitions
     */
    private function runLifecycle($user, $provider, $params)
    {
        // Instantiate controllers
        $userApiController = new UserApiController();
        $tripController = new TripController();

        // --- STEP 1: CREATE REQUEST ---
        $this->info("[STEP 1] User creating request...");
        \Auth::login($user);
        
        $request = Request::create('/api/user/send/request', 'POST', $params);
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');

        $response = $userApiController->send_request($request);

        // Check if there was an error
        $data = json_decode(json_encode($response->getData()), true);
        if (isset($data['error'])) {
            $this->error("Failed to create request: " . $data['error']);
            if (isset($data['message'])) {
                $this->error($data['message']);
            }
            return;
        }

        $requestId = $data['request_id'] ?? null;
        if (!$requestId) {
            $this->error("Failed to create request. Response: " . json_encode($data));
            return;
        }
        $this->info("Request Created! ID: {$requestId} | Status: SEARCHING");

        // Manually assign the filter if needed, to ensure our target driver gets it
        RequestFilter::updateOrCreate(
            ['request_id' => $requestId, 'provider_id' => $provider->id],
            ['status' => 0]
        );

        // --- STEP 2: PROVIDER ACCEPTS ---
        $this->info("[STEP 2] Provider accepting request...");
        \Auth::login($provider);
        \Auth::guard('provider')->setUser($provider);

        $acceptRequest = Request::create("/api/provider/trip/{$requestId}/accept", 'POST');
        $acceptRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $acceptRequest->headers->set('Accept', 'application/json');

        $response = $tripController->accept($acceptRequest, $requestId);
        
        // Response can be standard array or JSON response depending on context
        $data = json_decode(json_encode($response), true);
        if (isset($data['error'])) {
            $this->error("Provider accept failed: " . $data['error']);
            return;
        }

        $userRequest = UserRequests::find($requestId);
        $this->info("Request Accepted! Driver ID: {$userRequest->provider_id} | Status: {$userRequest->status}");

        // --- STEP 3: PROVIDER ARRIVES ---
        $this->info("[STEP 3] Provider arriving at pickup point...");
        $arriveRequest = Request::create("/api/provider/trip/{$requestId}", 'POST', ['status' => 'ARRIVED']);
        $arriveRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $arriveRequest->headers->set('Accept', 'application/json');

        $tripController->update($arriveRequest, $requestId);
        $userRequest = UserRequests::find($requestId);
        $this->info("Provider Arrived! Status: {$userRequest->status}");

        // --- STEP 4: PROVIDER STARTS RIDE (PICKEDUP) ---
        $this->info("[STEP 4] Starting ride (Picked up client)...");
        $startRequest = Request::create("/api/provider/trip/{$requestId}", 'POST', [
            'status' => 'PICKEDUP',
            'otp' => $userRequest->otp
        ]);
        $startRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $startRequest->headers->set('Accept', 'application/json');

        $tripController->update($startRequest, $requestId);
        $userRequest = UserRequests::find($requestId);
        $this->info("Ride Started! Status: {$userRequest->status} | Started At: {$userRequest->started_at}");

        // --- STEP 5: PROVIDER DROPS OFF ---
        $this->info("[STEP 5] Dropping off client (Arrived at destination)...");
        $dropParams = ['status' => 'DROPPED'];
        if ($userRequest->method === 'delivery') {
            $dropParams['otp'] = $userRequest->otp;
        }
        $dropRequest = Request::create("/api/provider/trip/{$requestId}", 'POST', $dropParams);
        $dropRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $dropRequest->headers->set('Accept', 'application/json');

        $tripController->update($dropRequest, $requestId);
        $userRequest = UserRequests::find($requestId);
        $this->info("Client Dropped Off! Status: {$userRequest->status}");

        // --- STEP 6: PAYMENT & COMPLETE ---
        $this->info("[STEP 6] Completing trip and verifying payment/wallet transactions...");
        $completeRequest = Request::create("/api/provider/trip/{$requestId}", 'POST', ['status' => 'COMPLETED']);
        $completeRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        $completeRequest->headers->set('Accept', 'application/json');

        $tripController->update($completeRequest, $requestId);
        
        $userRequest = UserRequests::find($requestId);
        $this->info("Trip Completed! Status: {$userRequest->status}");

        // Fetch invoice details
        $payment = DB::table('user_request_payments')->where('request_id', $requestId)->first();
        if ($payment) {
            $this->info("--- INVOICE DETAILS ---");
            $this->info("Fixed Fare: {$payment->fixed} CFA");
            $this->info("Distance Fare: {$payment->distance} CFA");
            $this->info("Commision deducted: {$payment->commision} CFA");
            $this->info("Total Fare: {$payment->total} CFA");
            $this->info("Provider Pay: {$payment->provider_pay} CFA");
            $this->info("Provider Commission: {$payment->provider_commission} CFA");
            $this->info("-----------------------");
        } else {
            $this->warn("No payment record found for this request!");
        }

        // Verify driver wallet updates (DAO Distribution validation)
        $providerAfter = Provider::find($provider->id);
        $this->info("Driver Balance after trip - wallet: " . ($providerAfter->eco_wallet_balance * 1000) . " CFA | eco_wallet: {$providerAfter->eco_wallet_balance} ECO");
    }
}
