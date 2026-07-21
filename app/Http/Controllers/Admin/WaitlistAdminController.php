<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceWaitlist;
use App\Services\WaitlistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaitlistAdminController extends Controller
{
    protected $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    /**
     * Display a listing of the waitlist.
     * GET /admin/waitlist
     */
    public function index(Request $request)
    {
        $query = ServiceWaitlist::with(['user', 'service', 'subscriptionPlan']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('zone')) {
            $query->where('zone', $request->input('zone'));
        }

        if ($request->has('service_id')) {
            $query->where('service_id', $request->input('service_id'));
        }

        $waitlist = $query->orderBy('position', 'asc')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $waitlist,
        ], 200);
    }

    /**
     * Get Waitlist KPIs
     * GET /admin/waitlist/kpis
     */
    public function kpis()
    {
        $totalWaiting = ServiceWaitlist::where('status', 'waiting')->count();
        $totalNotified = ServiceWaitlist::where('status', 'notified')->count();

        // Breakdown by Subscription Type (e.g. Work, School, Custom based on subscription_plan_id)
        // Since subscription type is mostly derived from the plan, we group by it.
        $byPlan = ServiceWaitlist::select('subscription_plan_id', DB::raw('count(*) as total'))
            ->where('status', 'waiting')
            ->groupBy('subscription_plan_id')
            ->with('subscriptionPlan')
            ->get();

        // Top 5 Zones by demand
        $topZones = ServiceWaitlist::select('zone', DB::raw('count(*) as demand'))
            ->where('status', 'waiting')
            ->groupBy('zone')
            ->orderBy('demand', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_waiting'  => $totalWaiting,
                'total_notified' => $totalNotified,
                'by_plan'        => $byPlan,
                'top_zones'      => $topZones,
            ]
        ], 200);
    }

    /**
     * Manually notify top N users in a specific service/zone
     * POST /admin/waitlist/notify
     */
    public function notify(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'limit'      => 'nullable|integer|min:1|max:100',
        ]);

        $limit = $validated['limit'] ?? 10;
        
        $notifiedCount = $this->waitlistService->notify($validated['service_id'], $limit);

        return response()->json([
            'success' => true,
            'message' => "{$notifiedCount} utilisateurs ont été notifiés avec succès.",
            'data'    => [
                'notified_count' => $notifiedCount
            ]
        ], 200);
    }
}
