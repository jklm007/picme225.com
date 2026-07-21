<?php

namespace App\Services;

use App\Models\AdCampaign;
use App\Models\AdPlatform;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service unifié pour gérer les campagnes sur différentes plateformes
 */
class AdPlatformService
{
    private $googleAdsService;
    private $facebookAdsService;
    private $tiktokAdsService;

    public function __construct(
        GoogleAdsService $googleAdsService,
        FacebookAdsService $facebookAdsService,
        TikTokAdsService $tiktokAdsService
    ) {
        $this->googleAdsService = $googleAdsService;
        $this->facebookAdsService = $facebookAdsService;
        $this->tiktokAdsService = $tiktokAdsService;
    }

    /**
     * Publier une campagne sur une ou plusieurs plateformes
     */
    public function publishCampaign(AdCampaign $campaign, array $platforms)
    {
        $results = [];

        foreach ($platforms as $platform) {
            try {
                $campaignData = $this->prepareCampaignData($campaign, $platform);
                
                switch($platform) {
                    case 'GOOGLE_ADS':
                        $result = $this->googleAdsService->createCampaign($campaignData);
                        break;
                    case 'FACEBOOK_ADS':
                        $result = $this->facebookAdsService->createCampaign($campaignData);
                        break;
                    case 'TIKTOK_ADS':
                        $result = $this->tiktokAdsService->createCampaign($campaignData);
                        break;
                    default:
                        throw new Exception("Plateforme non supportée: {$platform}");
                }

                // Enregistrer la plateforme
                AdPlatform::updateOrCreate(
                    [
                        'ad_campaign_id' => $campaign->id,
                        'platform' => $platform,
                    ],
                    [
                        'platform_campaign_id' => $result['campaign_id'],
                        'status' => $result['status'],
                        'platform_config' => $campaignData,
                    ]
                );

                $results[$platform] = $result;
            } catch (Exception $e) {
                Log::error("Error publishing to {$platform}: " . $e->getMessage());
                
                AdPlatform::updateOrCreate(
                    [
                        'ad_campaign_id' => $campaign->id,
                        'platform' => $platform,
                    ],
                    [
                        'status' => 'ERROR',
                        'error_message' => $e->getMessage(),
                    ]
                );

                $results[$platform] = [
                    'status' => 'ERROR',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Synchroniser les performances depuis les plateformes
     */
    public function syncPerformance(AdCampaign $campaign)
    {
        foreach ($campaign->platforms as $platform) {
            try {
                $performance = null;
                switch($platform->platform) {
                    case 'IN_APP':
                        $impressions = $campaign->impressions()->count();
                        $clicks = $campaign->clics()->count();
                        
                        // Récupération des coûts depuis les paramètres globaux (avec fallback)
                        $cpc = (float) \Setting::get('ad_in_app_cpc', 50);
                        $cpm = (float) \Setting::get('ad_in_app_cpm', 1000);
                        
                        // Calcul dynamique de la dépense selon l'objectif de la campagne
                        if (in_array($campaign->campaign_type, ['TRAFFIC', 'LEAD_GENERATION', 'SALES'])) {
                            // Facturation au Clic (CPC)
                            $spent = $clicks * $cpc;
                        } else {
                            // Facturation à l'impression (CPM) - ex: BRAND_AWARENESS
                            $spent = ($impressions / 1000) * $cpm;
                        }
                        
                        // Plafonner la dépense au budget total
                        if ($spent > $campaign->budget) {
                            $spent = $campaign->budget;
                        }
                        
                        $performance = [
                            'impressions' => $impressions,
                            'clicks' => $clicks,
                            'conversions' => 0,
                            'spent' => $spent,
                            'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                            'cpc' => $cpc,
                        ];
                        break;
                    case 'GOOGLE_ADS':
                        $performance = $this->googleAdsService->getCampaignPerformance($platform->platform_campaign_id);
                        break;
                    case 'FACEBOOK_ADS':
                        $performance = $this->facebookAdsService->getCampaignPerformance($platform->platform_campaign_id);
                        break;
                    case 'TIKTOK_ADS':
                        $performance = $this->tiktokAdsService->getCampaignPerformance($platform->platform_campaign_id);
                        break;
                }

                if ($performance) {
                    // Enregistrer les performances
                    \App\Models\CampaignPerformance::updateOrCreate(
                        [
                            'ad_campaign_id' => $campaign->id,
                            'ad_platform_id' => $platform->id,
                            'date' => now()->toDateString(),
                        ],
                        [
                            'impressions' => $performance['impressions'] ?? 0,
                            'clicks' => $performance['clicks'] ?? 0,
                            'conversions' => $performance['conversions'] ?? 0,
                            'spent' => $performance['spent'] ?? 0,
                            'ctr' => $performance['ctr'] ?? 0,
                            'cpc' => $performance['cpc'] ?? 0,
                        ]
                    );

                    // Mettre à jour le montant dépensé sur la plateforme
                    $platform->update([
                        'spent' => $performance['spent'] ?? 0,
                    ]);
                }
            } catch (Exception $e) {
                Log::error("Error syncing performance for {$platform->platform}: " . $e->getMessage());
            }
        }
    }

    /**
     * Préparer les données de campagne pour une plateforme
     */
    private function prepareCampaignData(AdCampaign $campaign, $platform)
    {
        $content = $campaign->contents->first();
        
        return [
            'name' => $campaign->name,
            'campaign_type' => $campaign->campaign_type,
            'budget' => $campaign->budget,
            'daily_budget' => $campaign->daily_budget,
            'start_date' => $campaign->start_date->format('Y-m-d'),
            'end_date' => $campaign->end_date ? $campaign->end_date->format('Y-m-d') : null,
            'target_audience' => $campaign->target_audience,
            'headline' => $content->headline ?? '',
            'description' => $content->description ?? '',
            'call_to_action' => $content->call_to_action ?? '',
            'image_url' => $content->image_url ?? '',
            'video_url' => $content->video_url ?? '',
            'keywords' => $content->keywords ?? [],
        ];
    }
}

