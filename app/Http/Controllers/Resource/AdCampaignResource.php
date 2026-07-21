<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\AdTemplate;
use App\Services\AiAdService;
use App\Services\AdPlatformService;
use Exception;

class AdCampaignResource extends Controller
{
    private $aiAdService;
    private $adPlatformService;

    public function __construct(AiAdService $aiAdService, AdPlatformService $adPlatformService)
    {
        $this->middleware('demo', ['only' => ['store', 'update', 'destroy']]);
        $this->aiAdService = $aiAdService;
        $this->adPlatformService = $adPlatformService;
    }

    public function index(Request $request)
    {
        $query = AdCampaign::with(['user', 'contents', 'platforms']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->ajax()) {
            return $campaigns;
        } else {
            return view('admin.ad-campaign.index', compact('campaigns'));
        }
    }

    public function create()
    {
        $templates = AdTemplate::where('is_active', true)->get();
        return view('admin.ad-campaign.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|in:BRAND_AWARENESS,LEAD_GENERATION,SALES,TRAFFIC,ENGAGEMENT',
            'budget' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'ad_slots' => 'required|array',
            'ad_slots.*' => 'exists:ad_slots,id',
            'media_file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4|max:10240',
            'call_to_action' => 'nullable|url',
            'platforms' => 'required|array',
            'platforms.*' => 'in:IN_APP,GOOGLE_ADS,FACEBOOK_ADS,TIKTOK_ADS',
            'business_type' => 'nullable|string',
            'target_audience' => 'nullable|string',
        ]);

        try {
            $data = $request->except(['platforms', 'media_file']);
            $data['status'] = 'ACTIVE';

            // Format target_audience into array if provided
            if (!empty($request->target_audience)) {
                $data['target_audience'] = array_map('trim', explode(',', $request->target_audience));
            }

            $campaign = AdCampaign::create($data);
            if ($request->has('ad_slots')) {
                $campaign->adSlots()->sync($request->ad_slots);
            }

            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
                $path = $file->store('ads', $disk);
                $mimeType = $file->getClientMimeType();
                $is_video = strpos($mimeType, 'video') !== false;

                \App\Models\AdContent::create([
                    'ad_campaign_id' => $campaign->id,
                    'content_type' => $is_video ? 'VIDEO' : 'IMAGE',
                    'image_url' => $is_video ? null : $path,
                    'video_url' => $is_video ? $path : null,
                    'call_to_action' => $request->call_to_action,
                ]);
            }

            // Publish to external platforms if selected
            $externalPlatforms = array_diff($request->platforms, ['IN_APP']);
            if (!empty($externalPlatforms)) {
                $this->adPlatformService->publishCampaign($campaign, $externalPlatforms);
            }
            
            // Record IN_APP platform if selected
            if (in_array('IN_APP', $request->platforms)) {
                \App\Models\AdPlatform::updateOrCreate([
                    'ad_campaign_id' => $campaign->id,
                    'platform' => 'IN_APP'
                ], [
                    'status' => 'ACTIVE'
                ]);
            }

            return redirect()->route('admin.ad-campaign.index')
                ->with('flash_success', 'Campagne et média créés avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $campaign = AdCampaign::with(['user', 'contents', 'platforms', 'performances'])
                ->findOrFail($id);
            return view('admin.ad-campaign.show', compact('campaign'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Campagne non trouvée');
        }
    }

    public function edit($id)
    {
        try {
            $campaign = AdCampaign::findOrFail($id);
            $templates = AdTemplate::where('is_active', true)->get();
            return view('admin.ad-campaign.edit', compact('campaign', 'templates'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Campagne non trouvée');
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|in:BRAND_AWARENESS,LEAD_GENERATION,SALES,TRAFFIC,ENGAGEMENT',
            'status' => 'required|in:DRAFT,ACTIVE,PAUSED,COMPLETED,CANCELLED',
            'budget' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'ad_slots' => 'required|array',
            'ad_slots.*' => 'exists:ad_slots,id',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:10240',
            'call_to_action' => 'nullable|url',
            'platforms' => 'required|array',
            'platforms.*' => 'in:IN_APP,GOOGLE_ADS,FACEBOOK_ADS,TIKTOK_ADS',
            'business_type' => 'nullable|string',
            'target_audience' => 'nullable|string',
        ]);

        try {
            $campaign = AdCampaign::findOrFail($id);
            $data = $request->except(['ad_slots', 'media_file', 'call_to_action', 'platforms']);

            // Format target_audience into array if provided
            if (!empty($request->target_audience)) {
                $data['target_audience'] = array_map('trim', explode(',', $request->target_audience));
            } else {
                $data['target_audience'] = null;
            }

            $campaign->update($data);
            
            if ($request->has('ad_slots')) {
                $campaign->adSlots()->sync($request->ad_slots);
            }

            // Mettre à jour le contenu (média + CTA)
            $content = $campaign->contents()->first();
            
            if ($request->hasFile('media_file')) {
                $file = $request->file('media_file');
                $disk = env('FILESYSTEM_DISK', config('filesystems.default', 's3'));
                $path = $file->store('ads', $disk);
                $mimeType = $file->getClientMimeType();
                $is_video = strpos($mimeType, 'video') !== false;

                if ($content) {
                    $content->update([
                        'content_type' => $is_video ? 'VIDEO' : 'IMAGE',
                        'image_url' => $is_video ? null : $path,
                        'video_url' => $is_video ? $path : null,
                        'call_to_action' => $request->call_to_action,
                    ]);
                } else {
                    \App\Models\AdContent::create([
                        'ad_campaign_id' => $campaign->id,
                        'content_type' => $is_video ? 'VIDEO' : 'IMAGE',
                        'image_url' => $is_video ? null : $path,
                        'video_url' => $is_video ? $path : null,
                        'call_to_action' => $request->call_to_action,
                    ]);
                }
            } elseif ($content && $request->has('call_to_action')) {
                // Just update the CTA if no new media
                $content->update([
                    'call_to_action' => $request->call_to_action,
                ]);
            } elseif (!$content && $request->has('call_to_action') && !empty($request->call_to_action)) {
                // If there's a CTA but no content exists yet, maybe just save the CTA with empty media (rare case)
                \App\Models\AdContent::create([
                    'ad_campaign_id' => $campaign->id,
                    'content_type' => 'IMAGE',
                    'image_url' => null,
                    'video_url' => null,
                    'call_to_action' => $request->call_to_action,
                ]);
            }

            // Sync IN_APP platform
            if (in_array('IN_APP', $request->platforms)) {
                \App\Models\AdPlatform::updateOrCreate([
                    'ad_campaign_id' => $campaign->id,
                    'platform' => 'IN_APP'
                ], [
                    'status' => 'ACTIVE'
                ]);
            } else {
                \App\Models\AdPlatform::where('ad_campaign_id', $campaign->id)->where('platform', 'IN_APP')->delete();
            }

            // Publish to external platforms if selected
            $externalPlatforms = array_diff($request->platforms, ['IN_APP']);
            if (!empty($externalPlatforms)) {
                $this->adPlatformService->publishCampaign($campaign, $externalPlatforms);
            }

            // We could optionally remove unselected platforms, but leaving them might be safer for historical tracking
            // or we can implement pausing them if removed. For now, we just publish the selected ones.

            return redirect()->route('admin.ad-campaign.index')
                ->with('flash_success', 'Campagne mise à jour avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Campagne non trouvée');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            AdCampaign::findOrFail($id)->delete();
            return back()->with('flash_success', 'Campagne supprimée avec succès');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Campagne non trouvée');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la suppression');
        }
    }

    /**
     * Synchroniser les performances
     */
    public function syncPerformance($id)
    {
        try {
            $campaign = AdCampaign::findOrFail($id);
            $this->adPlatformService->syncPerformance($campaign);
            return back()->with('flash_success', 'Performances synchronisées avec succès');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Erreur lors de la synchronisation');
        }
    }
    /**
     * Mettre à jour les paramètres de tarification internes
     */
    public function updateSettings(Request $request)
    {
        $this->validate($request, [
            'ad_in_app_cpc' => 'required|numeric|min:0',
            'ad_in_app_cpm' => 'required|numeric|min:0',
        ]);

        \Setting::set('ad_in_app_cpc', $request->ad_in_app_cpc);
        \Setting::set('ad_in_app_cpm', $request->ad_in_app_cpm);
        \Setting::save();

        return back()->with('flash_success', 'Paramètres de tarification mis à jour avec succès');
    }
}
