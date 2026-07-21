<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\FeatureFlag;
use App\Models\FleetCapacitySnapshot;
use App\Models\ServiceWaitlist;
use App\Models\User;
use App\Models\Provider;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class KpiController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Fetch key KPIs and metrics.
     *
     * GET /admin/kpis
     */
    public function index(Request $request)
    {
        try {
            // 1. Fleet Utilization Metrics
            $averageUtilization = FleetCapacitySnapshot::avg('utilization_rate') ?? 0;
            $maxUtilization = FleetCapacitySnapshot::max('utilization_rate') ?? 0;
            $totalSnapshots = FleetCapacitySnapshot::count();

            // Utilization by service
            $utilizationByService = FleetCapacitySnapshot::select('service_id', DB::raw('AVG(utilization_rate) as avg_utilization'), DB::raw('AVG(online_providers) as avg_online'), DB::raw('AVG(active_requests) as avg_requests'))
                ->groupBy('service_id')
                ->get()
                ->map(function ($item) {
                    $service = \App\Models\Service::find($item->service_id);
                    $item->service_name = $service ? $service->name : 'Inconnu';
                    return $item;
                });

            // 2. Waitlist KPI Metrics
            $waitlistStats = ServiceWaitlist::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Default states if not present in DB
            $statuses = ['waiting', 'notified', 'converted', 'expired', 'cancelled'];
            foreach ($statuses as $status) {
                if (!isset($waitlistStats[$status])) {
                    $waitlistStats[$status] = 0;
                }
            }

            $totalWaitlistEntries = array_sum($waitlistStats);
            $conversionRate = $totalWaitlistEntries > 0 ? ($waitlistStats['converted'] / $totalWaitlistEntries) * 100 : 0;

            // Waitlist count by service
            $waitlistByService = ServiceWaitlist::select('service_id', DB::raw('count(*) as count'))
                ->groupBy('service_id')
                ->get()
                ->map(function ($item) {
                    $service = \App\Models\Service::find($item->service_id);
                    $item->service_name = $service ? $service->name : 'Inconnu';
                    return $item;
                });

            // Average pickup wait time from snapshot table
            $avgPickupWaitTime = FleetCapacitySnapshot::avg('avg_wait_time_min') ?? 0;

            // 3. Feature Flags Metrics
            $totalFlags = FeatureFlag::count();
            $enabledFlags = FeatureFlag::where('is_enabled', true)->count();
            $disabledFlags = FeatureFlag::where('is_enabled', false)->count();
            $flagsByCategory = FeatureFlag::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get()
                ->pluck('count', 'category')
                ->toArray();

            // 4. Subscriptions Metrics
            $activeUserSubscriptions = User::whereNotNull('subscription_plan_id')
                ->where('subscription_expires_at', '>', Carbon::now())
                ->count();

            $activeProviderSubscriptions = Provider::whereNotNull('subscription_plan_id')
                ->where('subscription_expires_at', '>', Carbon::now())
                ->count();

            // Revenue from subscriptions (sum of subscription payments in passbook)
            $userSubscriptionRevenue = \App\Models\WalletPassbook::where('via', 'SUBSCRIPTION')
                ->where('status', 'DEBITED')
                ->sum('amount');
            $userSubscriptionRevenue = abs($userSubscriptionRevenue);

            return response()->json([
                'success' => true,
                'data' => [
                    'fleet_utilization' => [
                        'average_utilization_rate' => round($averageUtilization * 100, 2), // percentage
                        'max_utilization_rate'     => round($maxUtilization * 100, 2),
                        'total_snapshots_taken'    => $totalSnapshots,
                        'by_service'               => $utilizationByService,
                    ],
                    'waitlist' => [
                        'total_entries'       => $totalWaitlistEntries,
                        'status_breakdown'    => $waitlistStats,
                        'conversion_rate'     => round($conversionRate, 2),
                        'avg_pickup_wait_min' => round($avgPickupWaitTime, 2),
                        'by_service'          => $waitlistByService,
                    ],
                    'feature_flags' => [
                        'total_flags'   => $totalFlags,
                        'enabled'       => $enabledFlags,
                        'disabled'      => $disabledFlags,
                        'by_category'   => $flagsByCategory,
                    ],
                    'subscriptions' => [
                        'active_users'         => $activeUserSubscriptions,
                        'active_providers'     => $activeProviderSubscriptions,
                        'user_revenue_cfa'     => $userSubscriptionRevenue,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching KPIs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération des KPIs : ' . $e->getMessage()
            ], 500);
        }
    }
}
