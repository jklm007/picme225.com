<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdSlot;
use App\Models\AdCampaign;
use App\Models\AdImpression;
use App\Models\AdClick;
use App\Models\AdContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PrivateAdApiController extends Controller
{
    /**
     * Récupère une publicité active pour un emplacement (slot) spécifique.
     * Logique Hybride :
     * Si une pub privée active est configurée, elle est retournée (avec désactivation d'AdMob).
     * Sinon, le code d'unité AdMob configuré pour cet emplacement est renvoyé en fallback.
     *
     * GET /api/ad/fetch?slot_name=HOME_BANNER
     */
    public function fetchAd(Request $request)
    {
        $request->validate([
            'slot_name' => 'required|string',
        ]);

        try {
            $slotName = $request->slot_name ?? $request->route('slot');
            
            // 1. Rechercher l'emplacement publicitaire
            $slot = AdSlot::where('name', $slotName)->where('is_active', true)->first();
            if (!$slot) {
                return response()->json([
                    'type' => 'NONE',
                    'message' => 'Emplacement publicitaire inactif ou inexistant.'
                ]);
            }

            // 2. Rechercher une campagne privée active pour ce slot
            $now = now()->toDateString();
            $campaign = AdCampaign::whereHas('adSlots', function($q) use ($slot) {
                    $q->where('ad_slots.id', $slot->id);
                })
                ->where('status', 'ACTIVE')
                ->where('start_date', '<=', $now)
                ->where(function ($query) use ($now) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $now);
                })
                ->where(function ($query) {
                    $query->where('max_impressions', 0)
                          ->orWhereColumn('current_impressions', '<', 'max_impressions');
                })
                ->inRandomOrder()
                ->first();

            if ($campaign) {
                // Récupérer le premier contenu de type IMAGE ou TEXT
                $content = AdContent::where('ad_campaign_id', $campaign->id)->first();
                
                // Incrémenter les impressions
                $campaign->increment('current_impressions');

                // Enregistrer l'impression de manière asynchrone (ou directe ici pour simplifier)
                AdImpression::create([
                    'ad_campaign_id' => $campaign->id,
                    'user_id' => Auth::guard('api')->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // Vérifier si la campagne a atteint sa limite d'impressions suite à cette mise à jour
                if ($campaign->max_impressions > 0 && $campaign->current_impressions >= $campaign->max_impressions) {
                    $campaign->update(['status' => 'COMPLETED']);
                }

                return response()->json([
                    'type' => 'PRIVATE',
                    'campaign_id' => $campaign->id,
                    'title' => $campaign->name,
                    'headline' => $content->headline ?? null,
                    'description' => $content->description ?? null,
                    'image_url' => $content->image_url ? url($content->image_url) : null,
                    'video_url' => $content->video_url ? url($content->video_url) : null,
                    'is_video' => isset($content->content_type) && $content->content_type === 'VIDEO',
                    'target_url' => $content->call_to_action ?? null,
                ]);
            }

            // 3. Fallback AdMob si aucune publicité privée n'est active
            if ($slot->admob_unit_id) {
                return response()->json([
                    'type' => 'ADMOB',
                    'admob_unit_id' => $slot->admob_unit_id,
                ]);
            }

            return response()->json([
                'type' => 'NONE',
                'message' => 'Aucune publicité privée active et aucun code AdMob configuré.'
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching ad: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération de la publicité'], 500);
        }
    }

    /**
     * Enregistre un clic sur une publicité privée.
     *
     * POST /api/ad/click
     */
    public function recordClick(Request $request)
    {
        $request->validate([
            'campaign_id' => 'required|integer',
        ]);

        try {
            $campaign = AdCampaign::find($request->campaign_id);
            if (!$campaign) {
                return response()->json(['error' => 'Campagne introuvable'], 404);
            }

            // Incrémenter les clics
            $campaign->increment('current_clicks');

            // Enregistrer le clic
            AdClick::create([
                'ad_campaign_id' => $campaign->id,
                'user_id' => Auth::guard('api')->id(),
                'ip_address' => $request->ip(),
            ]);

            // Vérifier si la limite de clics est atteinte
            if ($campaign->max_clicks > 0 && $campaign->current_clicks >= $campaign->max_clicks) {
                $campaign->update(['status' => 'COMPLETED']);
            }

            return response()->json([
                'message' => 'Clic enregistré avec succès',
                'current_clicks' => $campaign->current_clicks
            ]);

        } catch (Exception $e) {
            Log::error('Error recording ad click: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur technique lors de l\'enregistrement du clic'], 500);
        }
    }
}
