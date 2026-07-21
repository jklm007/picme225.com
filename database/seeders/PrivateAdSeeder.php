<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Advertiser;
use App\Models\AdSlot;
use App\Models\AdCampaign;
use App\Models\AdContent;
use App\Models\User;

class PrivateAdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Advertisers
        $advertiser1 = Advertiser::updateOrCreate(
            ['email' => 'contact@orange.ci'],
            [
                'name' => 'Orange Côte d\'Ivoire',
                'company_name' => 'Orange CI S.A.',
                'phone' => '+225 07070707',
                'status' => 'ACTIVE',
            ]
        );

        $advertiser2 = Advertiser::updateOrCreate(
            ['email' => 'info@brasseries.ci'],
            [
                'name' => 'SOLIBRA',
                'company_name' => 'Société de Limonaderies et Brasseries d\'Afrique',
                'phone' => '+225 21212121',
                'status' => 'ACTIVE',
            ]
        );

        // 2. Seed Ad Slots (with official Google AdMob test IDs)
        $slot1 = AdSlot::updateOrCreate(
            ['name' => 'HOME_BANNER'],
            [
                'description' => 'Bannière publicitaire sur l\'écran d\'accueil',
                'admob_unit_id' => 'ca-app-pub-3940256099942544/6300978111', // Test Banner ID
                'is_active' => true,
            ]
        );

        $slot2 = AdSlot::updateOrCreate(
            ['name' => 'TRIP_COMPLETED'],
            [
                'description' => 'Pop-up interstitiel à la fin d\'une course',
                'admob_unit_id' => 'ca-app-pub-3940256099942544/1033173712', // Test Interstitial ID
                'is_active' => true,
            ]
        );

        // 3. Create a test campaign for Orange CI on HOME_BANNER
        $adminUser = User::first();
        if ($adminUser) {
            $campaign = AdCampaign::updateOrCreate(
                ['name' => 'Campagne Orange Max Pro'],
                [
                    'user_id' => $adminUser->id,
                    // 'advertiser_id' => $advertiser1->id,
                    // 'ad_slot_id' => $slot1->id,
                    'description' => 'Diffusion des forfaits mobiles Max Pro',
                    'status' => 'ACTIVE',
                    'campaign_type' => 'BRAND_AWARENESS',
                    'budget' => 500000.00,
                    'daily_budget' => 25000.00,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addMonths(2)->toDateString(),
                    // 'max_impressions' => 10000,
                    // 'max_clicks' => 500,
                    // 'current_impressions' => 0,
                    // 'current_clicks' => 0,
                ]
            );

            // Create contents for the campaign
            AdContent::updateOrCreate(
                ['ad_campaign_id' => $campaign->id],
                [
                    'content_type' => 'IMAGE',
                    'title' => 'Orange Max Pro',
                    'headline' => 'Restez connecté au meilleur prix',
                    'description' => 'Bénéficiez de 50Go d\'internet et appels illimités avec les forfaits Max Pro.',
                    'call_to_action' => 'https://www.orange.ci/maxpro',
                    'image_url' => 'https://votre-domaine.com/assets/ads/orange_max_pro.jpg',
                    'is_ai_generated' => false,
                ]
            );
        }
    }
}
