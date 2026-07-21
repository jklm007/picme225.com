<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\AdCampaign;
use App\Models\AdContent;
use App\Models\AdTemplate;
use App\Services\AiAdService;
use App\Services\AdPlatformService;
use Exception;

class AdCampaignController extends Controller
{
    private $aiAdService;
    private $adPlatformService;

    public function __construct(AiAdService $aiAdService, AdPlatformService $adPlatformService)
    {
        $this->aiAdService = $aiAdService;
        $this->adPlatformService = $adPlatformService;
    }

    /**
     * Liste des campagnes de l'utilisateur
     * GET /api/ad-campaigns
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $campaigns = AdCampaign::where('user_id', $user->id)
                ->with(['contents', 'platforms', 'performances'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($campaigns, 200);
        } catch (Exception $e) {
            Log::error('Error listing campaigns: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des campagnes'], 500);
        }
    }

    /**
     * Créer une nouvelle campagne
     * POST /api/ad-campaigns
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|in:BRAND_AWARENESS,LEAD_GENERATION,SALES,TRAFFIC,ENGAGEMENT',
            'budget' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'target_audience' => 'nullable|array',
            'platforms' => 'required|array',
            'platforms.*' => 'in:GOOGLE_ADS,FACEBOOK_ADS,TIKTOK_ADS,IN_APP,IN_VEHICLE',
            'use_ai' => 'nullable|boolean',
            'business_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $user = Auth::guard('api')->user();

            // Créer la campagne
            $campaign = AdCampaign::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'campaign_type' => $request->campaign_type,
                'budget' => $request->budget,
                'daily_budget' => $request->daily_budget,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'target_audience' => $request->target_audience,
                'status' => 'DRAFT',
                'is_ai_optimized' => $request->use_ai ?? false,
            ]);

            // Générer le contenu avec IA si demandé
            $content = null;
            if ($request->use_ai) {
                $aiContent = $this->aiAdService->generateAdContent(
                    $request->campaign_type,
                    $request->business_type ?? 'Général',
                    $request->target_audience ?? []
                );

                $content = AdContent::create([
                    'ad_campaign_id' => $campaign->id,
                    'content_type' => $request->content_type ?? 'TEXT',
                    'headline' => $aiContent['headline'] ?? null,
                    'description' => $aiContent['description'] ?? null,
                    'call_to_action' => $aiContent['call_to_action'] ?? null,
                    'keywords' => $aiContent['keywords'] ?? [],
                    'is_ai_generated' => true,
                    'ai_prompt' => json_encode($aiContent),
                ]);

                $campaign->update([
                    'ai_generated_content' => $aiContent,
                ]);
            } else {
                // Créer le contenu manuel
                $content = AdContent::create([
                    'ad_campaign_id' => $campaign->id,
                    'content_type' => $request->content_type ?? 'TEXT',
                    'headline' => $request->headline,
                    'description' => $request->description,
                    'call_to_action' => $request->call_to_action,
                    'image_url' => $request->image_url,
                    'video_url' => $request->video_url,
                    'keywords' => $request->keywords ?? [],
                ]);
            }

            // Publier sur les plateformes si demandé
            if ($request->publish && !empty($request->platforms)) {
                $this->adPlatformService->publishCampaign($campaign, $request->platforms);
                $campaign->update(['status' => 'ACTIVE']);
            }

            return response()->json([
                'message' => 'Campagne créée avec succès',
                'campaign' => $campaign->load(['contents', 'platforms']),
            ], 201);
        } catch (Exception $e) {
            Log::error('Error creating campaign: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la création de la campagne'], 500);
        }
    }

    /**
     * Obtenir les détails d'une campagne
     * GET /api/ad-campaigns/{id}
     */
    public function show($id)
    {
        try {
            $user = Auth::guard('api')->user();
            $campaign = AdCampaign::where('user_id', $user->id)
                ->with(['contents', 'platforms', 'performances'])
                ->findOrFail($id);

            return response()->json($campaign, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Campagne non trouvée'], 404);
        }
    }

    /**
     * Générer du contenu avec IA
     * POST /api/ad-campaigns/generate-content
     */
    public function generateContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_type' => 'required|in:BRAND_AWARENESS,LEAD_GENERATION,SALES,TRAFFIC,ENGAGEMENT',
            'business_type' => 'required|string',
            'target_audience' => 'nullable|array',
            'budget' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $content = $this->aiAdService->generateAdContent(
                $request->campaign_type,
                $request->business_type,
                $request->target_audience ?? [],
                $request->budget
            );

            // Optimiser pour différentes plateformes
            $optimized = [];
            foreach (['GOOGLE_ADS', 'FACEBOOK_ADS', 'TIKTOK_ADS'] as $platform) {
                $optimized[$platform] = $this->aiAdService->optimizeForPlatform($content, $platform);
            }

            return response()->json([
                'content' => $content,
                'optimized' => $optimized,
                'keywords' => $this->aiAdService->suggestKeywords(
                    $request->business_type,
                    $request->campaign_type,
                    $request->target_audience ?? []
                ),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error generating content: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la génération du contenu'], 500);
        }
    }

    /**
     * Publier une campagne sur des plateformes
     * POST /api/ad-campaigns/{id}/publish
     */
    public function publish(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'platforms' => 'required|array',
            'platforms.*' => 'in:GOOGLE_ADS,FACEBOOK_ADS,TIKTOK_ADS,IN_APP,IN_VEHICLE',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $user = Auth::guard('api')->user();
            $campaign = AdCampaign::where('user_id', $user->id)->findOrFail($id);

            $results = $this->adPlatformService->publishCampaign($campaign, $request->platforms);
            
            $campaign->update(['status' => 'ACTIVE']);

            return response()->json([
                'message' => 'Campagne publiée avec succès',
                'results' => $results,
                'campaign' => $campaign->load(['platforms']),
            ], 200);
        } catch (Exception $e) {
            Log::error('Error publishing campaign: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la publication'], 500);
        }
    }

    /**
     * Obtenir les performances d'une campagne
     * GET /api/ad-campaigns/{id}/performance
     */
    public function performance($id)
    {
        try {
            $user = Auth::guard('api')->user();
            $campaign = AdCampaign::where('user_id', $user->id)->findOrFail($id);

            // Synchroniser les performances
            $this->adPlatformService->syncPerformance($campaign);

            $campaign->load(['performances', 'platforms']);

            return response()->json([
                'campaign' => $campaign,
                'total_impressions' => $campaign->performances->sum('impressions'),
                'total_clicks' => $campaign->performances->sum('clicks'),
                'total_conversions' => $campaign->performances->sum('conversions'),
                'total_spent' => $campaign->total_spent,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des performances'], 500);
        }
    }

    /**
     * Obtenir les templates disponibles
     * GET /api/ad-campaigns/templates
     */
    public function templates(Request $request)
    {
        $query = AdTemplate::where('is_active', true);

        if ($request->campaign_type) {
            $query->where('campaign_type', $request->campaign_type);
        }

        if ($request->content_type) {
            $query->where('content_type', $request->content_type);
        }

        $templates = $query->get();

        return response()->json($templates, 200);
    }
}

