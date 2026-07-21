<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advertiser;
use App\Models\AdSlot;
use App\Models\AdCampaign;
use App\Models\AdImpression;
use App\Models\AdClick;
use Exception;
use Log;

class PrivateAdAdminController extends Controller
{
    // ==========================================
    // ANNONCEURS (Advertisers) CRUD
    // ==========================================

    public function listAdvertisers()
    {
        $advertisers = Advertiser::withCount('campaigns')->paginate(15);
        return response()->json($advertisers);
    }

    public function storeAdvertiser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:advertisers,email',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $advertiser = Advertiser::create($request->all());
        return response()->json(['message' => 'Annonceur créé avec succès', 'advertiser' => $advertiser], 201);
    }

    public function updateAdvertiser(Request $request, $id)
    {
        $advertiser = Advertiser::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:advertisers,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);

        $advertiser->update($request->all());
        return response()->json(['message' => 'Annonceur mis à jour', 'advertiser' => $advertiser]);
    }

    public function destroyAdvertiser($id)
    {
        $advertiser = Advertiser::findOrFail($id);
        $advertiser->delete();
        return response()->json(['message' => 'Annonceur supprimé']);
    }

    // ==========================================
    // EMPLACEMENTS (Ad Slots) CRUD
    // ==========================================

    public function listSlots()
    {
        $slots = AdSlot::all();
        return response()->json($slots);
    }

    public function storeSlot(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:ad_slots,name',
            'description' => 'nullable|string',
            'admob_unit_id' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $slot = AdSlot::create($request->all());
        return response()->json(['message' => 'Emplacement créé avec succès', 'slot' => $slot], 201);
    }

    public function updateSlot(Request $request, $id)
    {
        $slot = AdSlot::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:100|unique:ad_slots,name,' . $id,
            'description' => 'nullable|string',
            'admob_unit_id' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $slot->update($request->all());
        return response()->json(['message' => 'Emplacement mis à jour', 'slot' => $slot]);
    }

    public function destroySlot($id)
    {
        $slot = AdSlot::findOrFail($id);
        $slot->delete();
        return response()->json(['message' => 'Emplacement supprimé']);
    }

    // ==========================================
    // STATISTIQUES & RAPPORTS
    // ==========================================

    public function getGlobalStats()
    {
        try {
            $totalImpressions = AdImpression::count();
            $totalClicks = AdClick::count();
            $ctr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;

            // Stats par campagne
            $campaignStats = AdCampaign::with(['advertiser', 'adSlot'])
                ->get()
                ->map(function ($campaign) {
                    $ctr = $campaign->current_impressions > 0 
                        ? round(($campaign->current_clicks / $campaign->current_impressions) * 100, 2) 
                        : 0;
                    return [
                        'id' => $campaign->id,
                        'name' => $campaign->name,
                        'advertiser' => $campaign->advertiser->name ?? 'N/A',
                        'slot' => $campaign->adSlot->name ?? 'N/A',
                        'status' => $campaign->status,
                        'impressions' => $campaign->current_impressions,
                        'max_impressions' => $campaign->max_impressions,
                        'clicks' => $campaign->current_clicks,
                        'max_clicks' => $campaign->max_clicks,
                        'ctr' => $ctr . '%',
                    ];
                });

            return response()->json([
                'total_impressions' => $totalImpressions,
                'total_clicks' => $totalClicks,
                'ctr' => $ctr . '%',
                'campaigns' => $campaignStats,
            ]);
        } catch (Exception $e) {
            Log::error('Error generating global ad stats: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du calcul des statistiques'], 500);
        }
    }
}
