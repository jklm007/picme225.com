<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use App\Services\FeatureFlagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FeatureFlagController extends Controller
{
    /**
     * @var FeatureFlagService
     */
    protected $featureFlagService;

    /**
     * Create a new controller instance.
     *
     * @param  FeatureFlagService  $featureFlagService
     */
    public function __construct(FeatureFlagService $featureFlagService)
    {
        $this->featureFlagService = $featureFlagService;
    }

    /**
     * Display a listing of feature flags.
     *
     * GET /admin/feature-flags
     */
    public function index()
    {
        $flags = FeatureFlag::orderBy('key', 'asc')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $flags,
        ], 200);
    }

    /**
     * Store a newly created feature flag in storage.
     *
     * POST /admin/feature-flags/store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'                  => 'required|string|max:255|unique:feature_flags,key',
            'label'                => 'required|string|max:255',
            'description'          => 'nullable|string|max:1000',
            'is_enabled'           => 'nullable|boolean',
            'service_id'           => 'nullable|integer|exists:services,id',
            'zone'                 => 'nullable|string|max:255',
            'activation_conditions'=> 'nullable|array',
            'category'             => 'nullable|string|max:255',
        ]);

        $flag = FeatureFlag::create(array_merge([
            'is_enabled' => false,
            'zone'       => '*',
            'category'   => 'general',
        ], $validated));

        Log::info('Admin/FeatureFlagController: flag created', [
            'id'  => $flag->id,
            'key' => $flag->key,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature flag créé avec succès.',
            'data'    => $flag,
        ], 201);
    }

    /**
     * Update the specified feature flag in storage.
     *
     * PUT /admin/feature-flags/update/{key}
     */
    public function update(Request $request, $key)
    {
        $flag = FeatureFlag::where('key', $key)->firstOrFail();

        $validated = $request->validate([
            'label'                => 'required|string|max:255',
            'description'          => 'nullable|string|max:1000',
            'service_id'           => 'nullable|integer|exists:services,id',
            'zone'                 => 'nullable|string|max:255',
            'activation_conditions'=> 'nullable|array',
            'category'             => 'nullable|string|max:255',
        ]);

        $flag->update($validated);

        // Clear cache
        Cache::forget(FeatureFlag::cacheKey($key));
        Cache::forget(FeatureFlag::cacheKey($key, $flag->service_id));

        Log::info('Admin/FeatureFlagController: flag updated', [
            'key' => $key,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature flag mis à jour avec succès.',
            'data'    => $flag,
        ], 200);
    }

    /**
     * Toggle the feature flag enabled/disabled status.
     *
     * POST /admin/feature-flags/toggle/{key}
     */
    public function toggle(Request $request, $key)
    {
        $flag = FeatureFlag::where('key', $key)->firstOrFail();
        
        if ($flag->is_enabled) {
            $this->featureFlagService->disable($key);
        } else {
            $this->featureFlagService->enable($key);
        }

        $flag->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Status du flag changé.',
            'data'    => [
                'key'        => $flag->key,
                'is_enabled' => $flag->is_enabled,
            ],
        ], 200);
    }

    /**
     * Take a manual fleet capacity snapshot.
     *
     * POST /admin/feature-flags/snapshot
     */
    public function snapshot(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'zone'       => 'nullable|string|max:255',
        ]);

        $zone = $validated['zone'] ?? '*';
        $snapshot = $this->featureFlagService->snapshot((int) $validated['service_id'], $zone);

        return response()->json([
            'success' => true,
            'message' => 'Snapshot généré avec succès.',
            'data'    => $snapshot,
        ], 200);
    }

    /**
     * Auto activate a service feature flag based on current capacity.
     *
     * POST /admin/feature-flags/auto-activate
     */
    public function autoActivate(Request $request)
    {
        $validated = $request->validate([
            'service_id'    => 'required|integer|exists:services,id',
            'min_providers' => 'nullable|integer|min:1',
        ]);

        $minProviders = isset($validated['min_providers']) ? (int) $validated['min_providers'] : 5;
        $activated = $this->featureFlagService->autoActivate((int) $validated['service_id'], $minProviders);

        return response()->json([
            'success'   => true,
            'activated' => $activated,
            'message'   => $activated 
                ? 'Flag activé : capacité suffisante.' 
                : 'Flag non activé : capacité insuffisante.',
        ], 200);
    }
}
