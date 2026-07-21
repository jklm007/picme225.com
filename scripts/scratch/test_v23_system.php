<?php
require 'bootstrap/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Provider;
use App\User;
use App\DaoProposal;
use App\Services\FraudDetectionService;
use App\Services\SurgeEngineService;
use App\Services\DemandPredictionService;
use App\Services\DispatchEngine\ScoreService;
use App\Http\Controllers\Dao\ProposalController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

echo "==================================================\n";
echo "       PICME V2.3 INTEGRATION TEST SUITE           \n";
echo "==================================================\n\n";

$allPassed = true;

// Helper assert function
function assertTest($condition, $message) {
    global $allPassed;
    if ($condition) {
        echo "✅ PASS: $message\n";
    } else {
        echo "❌ FAIL: $message\n";
        $allPassed = false;
    }
}

// -----------------------------------------------------------------------------
// TEST 1: FraudDetectionService (Anti-Fraud GPS)
// -----------------------------------------------------------------------------
echo "--- Testing FraudDetectionService ---\n";
$fraudService = app(FraudDetectionService::class);
$provider = Provider::first() ?: new Provider();
$provider->id = 9999; // Mock ID

// 1.1 Test Mock Location GPS Ping
$mockPings = [
    ['lat' => 5.36, 'lng' => -4.008, 'speed_kmh' => 10, 'accuracy_meters' => 5, 'is_mock_location' => true, 'sensor_timestamp' => time(), 'device_fingerprint_hash' => 'hash1'],
    ['lat' => 5.3601, 'lng' => -4.0081, 'speed_kmh' => 12, 'accuracy_meters' => 5, 'is_mock_location' => false, 'sensor_timestamp' => time() + 10, 'device_fingerprint_hash' => 'hash1']
];
$misMock = $fraudService->computeMIS($provider, $mockPings);
assertTest($misMock === 0.0, "Mock location detection forces MIS to 0.0 (Actual: $misMock)");
assertTest($fraudService->getDispatchModifier($provider) === 0.0, "MIS = 0 forces dispatch modifier to 0.0");

// 1.2 Test Clean Normal Pings
Cache::forget("mis_{$provider->id}");
$cleanPings = [
    ['lat' => 5.3600, 'lng' => -4.0080, 'speed_kmh' => 30, 'accuracy_meters' => 5, 'is_mock_location' => false, 'sensor_timestamp' => time(), 'device_fingerprint_hash' => 'hash1'],
    ['lat' => 5.3605, 'lng' => -4.0085, 'speed_kmh' => 35, 'accuracy_meters' => 5, 'is_mock_location' => false, 'sensor_timestamp' => time() + 10, 'device_fingerprint_hash' => 'hash1']
];
$misClean = $fraudService->computeMIS($provider, $cleanPings);
assertTest($misClean > 80.0, "Clean position pings result in high MIS > 80 (Actual: $misClean)");
assertTest($fraudService->getDispatchModifier($provider) === 1.0, "High MIS > 80 gives full dispatch modifier 1.0");

// 1.3 Test Teleportation
Cache::forget("mis_{$provider->id}");
$teleportPings = [
    ['lat' => 5.3600, 'lng' => -4.0080, 'speed_kmh' => 30, 'accuracy_meters' => 5, 'is_mock_location' => false, 'sensor_timestamp' => time(), 'device_fingerprint_hash' => 'hash1'],
    ['lat' => 5.4800, 'lng' => -4.0080, 'speed_kmh' => 150, 'accuracy_meters' => 5, 'is_mock_location' => false, 'sensor_timestamp' => time() + 5, 'device_fingerprint_hash' => 'hash1'] // Jump of ~13km in 5 seconds
];
$misTeleport = $fraudService->computeMIS($provider, $teleportPings);
assertTest($misTeleport < 40.0, "Teleportation detection reduces MIS drastically (Actual: $misTeleport)");

echo "\n";

// -----------------------------------------------------------------------------
// TEST 2: SurgeEngineService
// -----------------------------------------------------------------------------
echo "--- Testing SurgeEngineService ---\n";
$surgeService = app(SurgeEngineService::class);
$geohash = "dr5r"; // Example geohash

// Clean cache first
Cache::forget("surge_{$geohash}");

$factor = $surgeService->getSurgeFactor($geohash);
assertTest($factor >= 1.0 && $factor <= 3.0, "Surge Factor always clamped between 1.0 and 3.0 (Actual: $factor)");

// Pricing test
$priceData = $surgeService->applyToPrice(2000.0, $geohash);
assertTest($priceData['final_price'] >= 2000.0, "Final price after surge is equal or higher than base price (Actual: {$priceData['final_price']})");
assertTest($priceData['driver_bonus'] >= 0, "Chauffeur bonus calculated correctly");

echo "\n";

// -----------------------------------------------------------------------------
// TEST 3: DemandPredictionService
// -----------------------------------------------------------------------------
echo "--- Testing DemandPredictionService ---\n";
$demandService = app(DemandPredictionService::class);

$hour = 14;
$day = 3; // Wednesday
$pds = $demandService->getPredictedDemandScore($geohash, $hour, $day);
assertTest($pds >= 0.0 && $pds <= 100.0, "Predicted Demand Score returns valid value [0-100] (Actual: $pds)");

echo "\n";

// -----------------------------------------------------------------------------
// TEST 4: ScoreService (V2.3 Dispatch Score)
// -----------------------------------------------------------------------------
echo "--- Testing ScoreService (V2.3 Formula) ---\n";
$scoreService = app(ScoreService::class);

$cleanProvider = Provider::first();
if ($cleanProvider) {
    $cleanProvider->priority = 500; // Raw priority
    $cleanProvider->rating = 4.8;
    $cleanProvider->latitude = 5.3600;
    $cleanProvider->longitude = -4.0080;
    
    // Set MIS score to clean (100)
    Cache::put("mis_{$cleanProvider->id}", 100.0, 10);
    // Zero fatigue
    Cache::forget("recent_trips_{$cleanProvider->id}");

    $tripContext = [
        's_lat' => 5.3610,
        's_lng' => -4.0082,
        'geohash' => 'dr5r'
    ];

    $scoreNormal = $scoreService->calculate($cleanProvider, $tripContext);
    assertTest($scoreNormal > 0 && $scoreNormal <= 100, "V2.3 score calculated successfully (Actual: $scoreNormal)");

    // Test with high recent trips fatigue
    Cache::put("recent_trips_{$cleanProvider->id}", 3, 10); // 3 trips = -75 pts activity component
    $scoreFatigued = $scoreService->calculate($cleanProvider, $tripContext);
    assertTest($scoreFatigued < $scoreNormal, "Activity Fatigue correctly lowers dispatch score (Normal: $scoreNormal vs Fatigued: $scoreFatigued)");

    // Test with low MIS (Fraudulent)
    Cache::put("mis_{$cleanProvider->id}", 25.0, 10); // MIS 25 = 0.0 dispatch modifier
    $scoreFraud = $scoreService->calculate($cleanProvider, $tripContext);
    assertTest($scoreFraud === 0.0, "Low MIS (Suspicious/Fraud) completely drops dispatch score to 0.0 (Actual: $scoreFraud)");
} else {
    echo "⚠️ Skipped ScoreService test: No providers in DB.\n";
}

echo "\n";

// -----------------------------------------------------------------------------
// TEST 5: DAO Quadratic Voting
// -----------------------------------------------------------------------------
echo "--- Testing DAO Quadratic Voting ---\n";
$user = User::first();
if ($user) {
    $proposal = DaoProposal::first();
    if (!$proposal) {
        // Create mock active proposal for testing
        $proposal = DaoProposal::create([
            'blockchain_proposal_id' => '1',
            'user_id' => $user->id,
            'type' => 'PRICE_CHANGE',
            'title' => 'Test Proposal',
            'description' => 'Test proposal description',
            'status' => 'ACTIVE',
            'start_time' => now(),
            'end_time' => now()->addDays(7)
        ]);
    }

    // Set wallet
    if (!$user->wallet_address) {
        $user->wallet_address = '0x1234567890123456789012345678901234567890';
        $user->save();
    }

    // Test vote quadratic cost calculations
    // Case 1: 1 Vote => Cost = 1^2 * 0.1 = 0.1 ECO
    $user->eco_token_balance = 100.0; // Gift ECO for testing
    $user->save();

    $request1 = \Illuminate\Http\Request::create('/vote', 'POST', ['vote' => 'FOR', 'votes_count' => 1]);
    $controller = app(ProposalController::class);
    
    // Clear any previous votes to ensure we can vote
    $proposal->votes()->where('user_id', $user->id)->delete();
    
    // Log in user
    Auth::guard('api')->setUser($user);

    $response1 = $controller->vote($request1, $proposal->id);
    assertTest($response1->getStatusCode() === 201, "Successful quadratic vote with 1 vote");
    $user->refresh();
    assertTest(abs($user->eco_token_balance - 99.9) < 0.0001, "1 Vote cost exactly 0.1 ECO (Wallet: {$user->eco_token_balance})");

    // Case 2: 5 Votes => Cost = 5^2 * 0.1 = 2.5 ECO
    $proposal->votes()->where('user_id', $user->id)->delete();
    $user->eco_token_balance = 100.0;
    $user->save();

    $request2 = \Illuminate\Http\Request::create('/vote', 'POST', ['vote' => 'FOR', 'votes_count' => 5]);
    $response2 = $controller->vote($request2, $proposal->id);
    assertTest($response2->getStatusCode() === 201, "Successful quadratic vote with 5 votes");
    $user->refresh();
    assertTest(abs($user->eco_token_balance - 97.5) < 0.0001, "5 Votes cost exactly 2.5 ECO (Wallet: {$user->eco_token_balance})");

    // Case 3: 10 Votes => Cost = 10^2 * 0.1 = 10.0 ECO
    $proposal->votes()->where('user_id', $user->id)->delete();
    $user->eco_token_balance = 100.0;
    $user->save();

    $request3 = \Illuminate\Http\Request::create('/vote', 'POST', ['vote' => 'FOR', 'votes_count' => 10]);
    $response3 = $controller->vote($request3, $proposal->id);
    assertTest($response3->getStatusCode() === 201, "Successful quadratic vote with 10 votes");
    $user->refresh();
    assertTest(abs($user->eco_token_balance - 90.0) < 0.0001, "10 Votes cost exactly 10.0 ECO (Wallet: {$user->eco_token_balance})");

    // Case 4: Proposal Creation with Geolocation & Commune for STOP_ADDITION
    echo "--- Testing DAO STOP_ADDITION Proposal Creation ---\n";
    $requestCreate = \Illuminate\Http\Request::create('/proposals', 'POST', [
        'type' => 'STOP_ADDITION',
        'title' => 'Nouveau stop Riviera 3',
        'description' => 'Ajout de stop pour fluidifier les trajets de Riviera 3 vers le Plateau',
        'execution_data' => [
            'latitude' => 5.3582,
            'longitude' => -3.9744,
            'address' => 'Riviera 3, Abidjan',
            'name' => 'Riviera 3 Stop',
            'commune' => 'Cocody'
        ]
    ]);
    
    // Clear user wallet address check bypass
    if (!$user->wallet_address) {
        $user->wallet_address = '0x1234567890123456789012345678901234567890';
    }
    // Grant balance
    $user->eco_token_balance = 100.0;
    $user->save();

    // Mock Web3Service to avoid actual blockchain call
    $mockWeb3 = Mockery::mock(\App\Services\Blockchain\Web3Service::class);
    $mockWeb3->shouldReceive('createProposal')->andReturn(['proposal_id' => 'mock_block_123']);
    app()->instance(\App\Services\Blockchain\Web3Service::class, $mockWeb3);

    $responseCreate = $controller->store($requestCreate);
    if ($responseCreate->getStatusCode() !== 201) {
        echo "Response error content: " . $responseCreate->getContent() . "\n";
    }
    assertTest($responseCreate->getStatusCode() === 201, "STOP_ADDITION Proposal created successfully with coordinates and commune (Status: " . $responseCreate->getStatusCode() . ")");
    
    $proposalData = json_decode($responseCreate->getContent(), true);
    assertTest(isset($proposalData['proposal']['execution_data']['commune']), "Commune Riviera 3 correctly stored in execution_data");
    assertTest($proposalData['proposal']['execution_data']['latitude'] === 5.3582, "Latitude matches Riviera 3 coordinates exactly");

    // Case 5: Driver (Provider) Quadratic Voting
    echo "--- Testing DAO Driver (Provider) Quadratic Voting ---\n";
    $provider = Provider::first();
    if ($provider) {
        // Set wallet and balance
        if (!$provider->wallet_address) {
            $provider->wallet_address = '0x9876543210987654321098765432109876543210';
        }
        $provider->eco_wallet_balance = 100.0;
        $provider->save();

        // Log in driver
        Auth::guard('api')->setUser($provider);

        // Create mock active proposal for voting if Riviera proposal was deleted or use the Test one
        $voteProposal = DaoProposal::create([
            'blockchain_proposal_id' => 'mock_driver_vote_prop',
            'user_id' => $user->id,
            'creator_type' => 'USER',
            'type' => 'PRICE_CHANGE',
            'title' => 'Driver Vote Test Proposal',
            'description' => 'Driver Vote Test proposal description',
            'status' => 'ACTIVE',
            'start_time' => now(),
            'end_time' => now()->addDays(7)
        ]);

        $requestDriverVote = \Illuminate\Http\Request::create('/vote', 'POST', ['vote' => 'FOR', 'votes_count' => 5]);
        $responseDriverVote = $controller->vote($requestDriverVote, $voteProposal->id);
        
        assertTest($responseDriverVote->getStatusCode() === 201, "Driver (Provider) successful quadratic vote with 5 votes");
        $provider->refresh();
        assertTest(abs($provider->eco_wallet_balance - 97.5) < 0.0001, "Driver 5 Votes cost exactly 2.5 ECO (Wallet: {$provider->eco_wallet_balance} ECO)");

        // Verify stored DB entry voter_type is 'PROVIDER'
        $driverVoteDb = $voteProposal->votes()->where('user_id', $provider->id)->where('voter_type', 'PROVIDER')->first();
        assertTest($driverVoteDb !== null, "Stored vote has voter_type = 'PROVIDER'");
        assertTest($driverVoteDb->votes_weight === 5, "Stored vote has weight = 5");

        // Clean up
        $voteProposal->votes()->delete();
        $voteProposal->delete();
    } else {
        echo "⚠️ Skipped Driver vote test: No providers in DB.\n";
    }

    // Clean up created mock proposal & stop
    if (isset($proposalData['proposal']['id'])) {
        $p = DaoProposal::find($proposalData['proposal']['id']);
        if ($p) {
            $p->delete();
        }
    }
    \App\PdpStop::where('name', 'Riviera 3 Stop')->delete();

    // Clean up mock proposal if it was created
    if ($proposal->title === 'Test Proposal') {
        $proposal->votes()->delete();
        $proposal->delete();
    }
} else {
    echo "⚠️ Skipped DAO vote test: No users in DB.\n";
}

echo "\n==================================================\n";
if ($allPassed) {
    echo "🎉 ALL TESTS PASSED SUCCESSFULLY! PICME V2.3 READY!\n";
} else {
    echo "🚨 SOME TESTS FAILED! PLEASE REVIEW LOGS!\n";
}
echo "==================================================\n";
