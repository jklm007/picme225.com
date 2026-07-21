<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AdSlot;
use App\Models\AdCampaign;
use App\Models\Advertiser;
use App\Models\AdContent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PrivateAdApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure test data exists
        $this->advertiser = Advertiser::firstOrCreate(
            ['email' => 'test@adv.com'],
            ['name' => 'Test Advertiser', 'company_name' => 'Adv Co', 'phone' => '123', 'status' => 'ACTIVE']
        );

        $this->bannerSlot = AdSlot::firstOrCreate(
            ['name' => 'TEST_HOME_BANNER'],
            ['description' => 'Test Banner', 'admob_unit_id' => 'admob_banner_123', 'is_active' => true]
        );

        $this->interstitialSlot = AdSlot::firstOrCreate(
            ['name' => 'TEST_INTERSTITIAL'],
            ['description' => 'Test Interstitial', 'admob_unit_id' => 'admob_interstitial_456', 'is_active' => true]
        );

        $admin = User::first() ?: User::factory()->create();

        // Active private ad for Banner
        $this->campaign = AdCampaign::create([
            'user_id' => $admin->id,
            'advertiser_id' => $this->advertiser->id,
            'ad_slot_id' => $this->bannerSlot->id,
            'name' => 'Private Test Ad',
            'status' => 'ACTIVE',
            'campaign_type' => 'BRAND_AWARENESS',
            'budget' => 10000.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'max_impressions' => 10,
            'max_clicks' => 5,
        ]);

        AdContent::create([
            'ad_campaign_id' => $this->campaign->id,
            'content_type' => 'IMAGE',
            'title' => 'Test Ad Title',
            'headline' => 'Test Headline',
            'description' => 'Test Description',
            'call_to_action' => 'http://click.me',
            'image_url' => 'http://image.url',
        ]);
    }

    /**
     * Test fetching a private ad (should return PRIVATE and ad details, bypassing AdMob).
     */
    public function test_fetch_active_private_ad()
    {
        $response = $this->json('GET', '/api/ad/fetch', [
            'slot_name' => 'TEST_HOME_BANNER'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'type' => 'PRIVATE',
                'campaign_id' => $this->campaign->id,
                'title' => 'Private Test Ad',
                'headline' => 'Test Headline',
                'image_url' => 'http://image.url',
                'target_url' => 'http://click.me'
            ]);

        // Verify impression was logged
        $this->campaign->refresh();
        $this->assertEquals(1, $this->campaign->current_impressions);
    }

    /**
     * Test fetching an ad when no private ad is active (should fallback to ADMOB).
     */
    public function test_fetch_fallback_admob()
    {
        $response = $this->json('GET', '/api/ad/fetch', [
            'slot_name' => 'TEST_INTERSTITIAL'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'type' => 'ADMOB',
                'admob_unit_id' => 'admob_interstitial_456'
            ]);
    }

    /**
     * Test tracking ad clicks.
     */
    public function test_record_ad_click()
    {
        $response = $this->json('POST', '/api/ad/click', [
            'campaign_id' => $this->campaign->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Clic enregistré avec succès',
                'current_clicks' => 1
            ]);

        $this->campaign->refresh();
        $this->assertEquals(1, $this->campaign->current_clicks);
    }
}
