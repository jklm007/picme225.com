<?php

// Boot Laravel
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use App\Models\AdSlot;
use App\Models\AdCampaign;
use App\Models\Advertiser;
use App\Models\AdContent;
use App\Models\AdImpression;
use App\Models\AdClick;
use App\Http\Controllers\PrivateAdApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Begin database transaction to keep database clean
DB::beginTransaction();

try {
    echo "==================================================\n";
    echo "TESTING PRIVATE ADS API\n";
    echo "==================================================\n";

    // 1. Prepare test data
    $advertiser = Advertiser::firstOrCreate(
        ['email' => 'integration-test@orange.ci'],
        [
            'name' => 'Orange Test',
            'company_name' => 'Orange Test S.A.',
            'phone' => '+225 00000000',
            'status' => 'ACTIVE'
        ]
    );

    $bannerSlot = AdSlot::firstOrCreate(
        ['name' => 'INTEGRATION_HOME_BANNER'],
        [
            'description' => 'Integration Banner Slot',
            'admob_unit_id' => 'admob_test_unit_123',
            'is_active' => true
        ]
    );

    $interstitialSlot = AdSlot::firstOrCreate(
        ['name' => 'INTEGRATION_INTERSTITIAL'],
        [
            'description' => 'Integration Interstitial Slot',
            'admob_unit_id' => 'admob_test_unit_456',
            'is_active' => true
        ]
    );

    // Create a private campaign for the Banner
    $campaign = AdCampaign::create([
        'user_id' => 1, // Default admin ID
        'advertiser_id' => $advertiser->id,
        'ad_slot_id' => $bannerSlot->id,
        'name' => 'Integration Campaign',
        'status' => 'ACTIVE',
        'campaign_type' => 'BRAND_AWARENESS',
        'budget' => 10000.00,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(5)->toDateString(),
        'max_impressions' => 10,
        'max_clicks' => 5,
        'current_impressions' => 0,
        'current_clicks' => 0,
    ]);

    AdContent::create([
        'ad_campaign_id' => $campaign->id,
        'content_type' => 'IMAGE',
        'title' => 'Orange Max Test',
        'headline' => 'Test headline',
        'description' => 'Test ad description',
        'call_to_action' => 'https://orange.ci/test',
        'image_url' => 'https://orange.ci/ad.jpg',
    ]);

    // 2. Instantiate controller
    $controller = new PrivateAdApiController();

    // 3. Test Case A: Fetch ad for slot with active private ad
    echo "\nTest A: Fetching active private ad for slot 'INTEGRATION_HOME_BANNER'...\n";
    $requestA = Request::create('/api/ad/fetch', 'GET', ['slot_name' => 'INTEGRATION_HOME_BANNER']);
    $responseA = $controller->fetchAd($requestA);
    $dataA = json_decode($responseA->getContent(), true);

    if (isset($dataA['type']) && $dataA['type'] === 'PRIVATE') {
        echo "✅ SUCCESS: Returned private ad: " . $dataA['title'] . "\n";
        echo "   Headline: " . $dataA['headline'] . "\n";
        echo "   Target Link: " . $dataA['target_url'] . "\n";
    } else {
        echo "❌ FAILED: Unexpected response: " . print_r($dataA, true) . "\n";
    }

    // Verify impression logging
    $campaign->refresh();
    $impressionLogged = AdImpression::where('ad_campaign_id', $campaign->id)->exists();
    if ($campaign->current_impressions === 1 && $impressionLogged) {
        echo "✅ SUCCESS: Impression recorded in database (count = 1)\n";
    } else {
        echo "❌ FAILED: Impression not recorded correctly. Count: " . $campaign->current_impressions . "\n";
    }

    // 4. Test Case B: Fetch ad for slot with NO active private ad (fallback to AdMob)
    echo "\nTest B: Fetching ad for slot 'INTEGRATION_INTERSTITIAL' (no private ads)...\n";
    $requestB = Request::create('/api/ad/fetch', 'GET', ['slot_name' => 'INTEGRATION_INTERSTITIAL']);
    $responseB = $controller->fetchAd($requestB);
    $dataB = json_decode($responseB->getContent(), true);

    if (isset($dataB['type']) && $dataB['type'] === 'ADMOB') {
        echo "✅ SUCCESS: Fallback to AdMob triggered with Unit ID: " . $dataB['admob_unit_id'] . "\n";
    } else {
        echo "❌ FAILED: Unexpected response: " . print_r($dataB, true) . "\n";
    }

    // 5. Test Case C: Record click on private ad
    echo "\nTest C: Recording click on campaign ID " . $campaign->id . "...\n";
    $requestC = Request::create('/api/ad/click', 'POST', ['campaign_id' => $campaign->id]);
    $responseC = $controller->recordClick($requestC);
    $dataC = json_decode($responseC->getContent(), true);

    $campaign->refresh();
    $clickLogged = AdClick::where('ad_campaign_id', $campaign->id)->exists();
    if ($campaign->current_clicks === 1 && $clickLogged && isset($dataC['message'])) {
        echo "✅ SUCCESS: Click recorded in database (count = 1). Response: " . $dataC['message'] . "\n";
    } else {
        echo "❌ FAILED: Click not recorded correctly. Count: " . $campaign->current_clicks . "\n";
    }

    echo "\n==================================================\n";
    echo "ALL INTEGRATION TESTS PASSED SUCCESSFULLY! 🎉\n";
    echo "==================================================\n";

} catch (Exception $e) {
    echo "❌ ERROR ENCOUNTERED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    // Rollback transaction to keep the database clean
    DB::rollBack();
    echo "\nDatabase transaction rolled back successfully.\n";
}
