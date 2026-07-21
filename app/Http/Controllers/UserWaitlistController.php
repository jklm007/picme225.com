<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\WaitlistService;

class UserWaitlistController extends Controller
{
    /**
     * @var WaitlistService
     */
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    // -----------------------------------------------------------------------
    // Endpoints
    // -----------------------------------------------------------------------

    /**
     * Join the waitlist for a service.
     *
     * POST /api/user/waitlist/join
     *
     * Body:
     *   service_id       required  integer  exists in services table
     *   latitude         optional  numeric
     *   longitude        optional  numeric
     *   preferred_time   optional  string
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'service_id'     => 'required|integer|exists:services,id',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'preferred_time' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        if (!\App\Models\FeatureFlag::isEnabled('waitlist_enabled')) {
            return response()->json([
                'success' => false,
                'message' => 'Le service de liste d\'attente est actuellement désactivé.',
            ], 403);
        }

        $data = array_filter([
            'latitude'             => $validated['latitude']       ?? null,
            'longitude'            => $validated['longitude']      ?? null,
            'preferred_time'       => $validated['preferred_time'] ?? null,
            'subscription_plan_id' => $user->subscription_plan_id ?? null,
        ], fn($v) => $v !== null);

        $entry = $this->waitlistService->join(
            $user->id,
            (int) $validated['service_id'],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Vous avez rejoint la liste d\'attente.',
            'data'    => $entry,
        ], 200);
    }

    /**
     * Leave the waitlist for a service.
     *
     * POST /api/user/waitlist/leave
     *
     * Body:
     *   service_id  required  integer  exists in services table
     */
    public function leave(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $user = Auth::user();

        $result = $this->waitlistService->leave(
            $user->id,
            (int) $validated['service_id']
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas sur la liste d\'attente pour ce service.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vous avez quitté la liste d\'attente.',
        ], 200);
    }

    /**
     * Get the user's current waitlist status for a service.
     *
     * GET /api/user/waitlist/status?service_id=1
     *
     * Query params:
     *   service_id  required  integer  exists in services table
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $user = Auth::user();

        $status = $this->waitlistService->status(
            $user->id,
            (int) $validated['service_id']
        );

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune entrée de liste d\'attente trouvée.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $status,
        ], 200);
    }
}
