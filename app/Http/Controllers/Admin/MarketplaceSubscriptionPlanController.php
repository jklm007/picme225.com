<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Admin\MarketplaceSubscriptionPlanController
 *
 * Manages fixed-price Marketplace subscription plans (target = 'marketplace').
 * Completely separate from the driver/provider plans managed by SubscriptionPlanController.
 *
 * Routes prefix: admin/marketplace-subscription-plans
 */
class MarketplaceSubscriptionPlanController extends Controller
{
    /**
     * GET admin/marketplace-subscription-plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::forMarketplace()
            ->orderBy('price', 'asc')
            ->get();

        // Attach live subscriber count for display
        foreach ($plans as $plan) {
            $plan->active_subscribers = User::where('marketplace_plan_id', $plan->id)
                ->where('marketplace_plan_expires_at', '>', now())
                ->count();
        }

        $totalActiveSubscribers = $plans->sum('active_subscribers');
        $totalMonthlyRevenue    = $plans->sum(fn($p) => $p->price * $p->active_subscribers);

        return view('admin.marketplace_subscription_plans.index', compact(
            'plans',
            'totalActiveSubscribers',
            'totalMonthlyRevenue'
        ));
    }

    /**
     * GET admin/marketplace-subscription-plans/create
     */
    public function create()
    {
        return view('admin.marketplace_subscription_plans.create');
    }

    /**
     * POST admin/marketplace-subscription-plans
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'                     => 'required|string|max:100|unique:subscription_plans,name',
            'description'              => 'nullable|string|max:1000',
            'price'                    => 'required|numeric|min:0',
            'period'                   => 'required|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'commission_type'          => 'required|in:fixed,percentage',
            'commission_value'         => 'required|numeric|min:0',
            'max_categories'           => 'required|integer|min:1',
            'priority'                 => 'required|integer|min:0',
            'insurance_included'       => 'required|boolean',
            'staking_bonus_percentage' => 'nullable|numeric|min:0|max:50',
        ]);

        SubscriptionPlan::create([
            'name'                     => strtoupper($request->name),
            'target'                   => 'marketplace',
            'description'              => $request->description,
            'price'                    => $request->price,
            'period'                   => $request->period,
            'commission_type'          => $request->commission_type,
            'commission_value'         => $request->commission_value,
            'fixed_fee'                => $request->fixed_fee ?? 0,
            'max_categories'           => $request->max_categories,
            'priority'                 => $request->priority,
            'priority_weight'          => $request->priority_weight ?? 0,
            'insurance_included'       => $request->insurance_included,
            'staking_bonus_percentage' => $request->staking_bonus_percentage ?? 0,
            'show_on_marketplace'      => true,
            'status'                   => 1,
        ]);

        return redirect()->route('admin.marketplace-subscription-plans.index')
            ->with('flash_success', 'Plan Marketplace créé avec succès.');
    }

    /**
     * GET admin/marketplace-subscription-plans/{id}/edit
     */
    public function edit($id)
    {
        $plan = SubscriptionPlan::forMarketplace()->findOrFail($id);

        $recentSubscribers = User::where('marketplace_plan_id', $plan->id)
            ->where('marketplace_plan_expires_at', '>', now())
            ->with(['profile'])
            ->orderBy('marketplace_plan_expires_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.marketplace_subscription_plans.edit', compact('plan', 'recentSubscribers'));
    }

    /**
     * PUT admin/marketplace-subscription-plans/{id}
     */
    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::forMarketplace()->findOrFail($id);

        $this->validate($request, [
            'name'                     => 'required|string|max:100|unique:subscription_plans,name,' . $id,
            'description'              => 'nullable|string|max:1000',
            'price'                    => 'required|numeric|min:0',
            'period'                   => 'required|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'commission_type'          => 'required|in:fixed,percentage',
            'commission_value'         => 'required|numeric|min:0',
            'max_categories'           => 'required|integer|min:1',
            'priority'                 => 'required|integer|min:0',
            'insurance_included'       => 'required|boolean',
            'staking_bonus_percentage' => 'nullable|numeric|min:0|max:50',
            'status'                   => 'required|boolean',
        ]);

        $plan->update([
            'name'                     => strtoupper($request->name),
            'description'              => $request->description,
            'price'                    => $request->price,
            'period'                   => $request->period,
            'commission_type'          => $request->commission_type,
            'commission_value'         => $request->commission_value,
            'fixed_fee'                => $request->fixed_fee ?? 0,
            'max_categories'           => $request->max_categories,
            'priority'                 => $request->priority,
            'priority_weight'          => $request->priority_weight ?? 0,
            'insurance_included'       => $request->insurance_included,
            'staking_bonus_percentage' => $request->staking_bonus_percentage ?? 0,
            'status'                   => $request->status,
        ]);

        return redirect()->route('admin.marketplace-subscription-plans.index')
            ->with('flash_success', 'Plan Marketplace mis à jour avec succès.');
    }

    /**
     * DELETE admin/marketplace-subscription-plans/{id}
     * Soft-deactivation only — never hard delete.
     */
    public function destroy($id)
    {
        $plan = SubscriptionPlan::forMarketplace()->findOrFail($id);

        $activeCount = User::where('marketplace_plan_id', $plan->id)
            ->where('marketplace_plan_expires_at', '>', now())
            ->count();

        if ($activeCount > 0) {
            return redirect()->back()->with('flash_error',
                "Impossible de supprimer : {$activeCount} utilisateur(s) actif(s) sur ce plan.");
        }

        $plan->update(['status' => 0]);

        return redirect()->route('admin.marketplace-subscription-plans.index')
            ->with('flash_success', 'Plan Marketplace désactivé avec succès.');
    }

    /**
     * POST admin/marketplace-subscription-plans/{id}/toggle
     */
    public function toggleStatus($id)
    {
        $plan = SubscriptionPlan::forMarketplace()->findOrFail($id);
        $plan->update(['status' => ! $plan->status]);

        $label = $plan->status ? 'activé' : 'désactivé';
        return redirect()->back()->with('flash_success', "Plan {$label} avec succès.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Active Transport Schedules Overview
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET admin/marketplace-subscription-plans/transport-schedules
     * Overview of all active VTC commute subscriptions (dynamic plans).
     */
    public function transportSchedules(Request $request)
    {
        $query = \App\Models\UserSubscriptionSchedule::with(['user', 'serviceType'])
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) => $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%"));
        }

        $schedules = $query->orderBy('expires_at', 'asc')->paginate(25);

        $stats = [
            'total_active'          => \App\Models\UserSubscriptionSchedule::where('status', 'ACTIVE')->where('expires_at', '>', now())->count(),
            'expiring_in_3_days'    => \App\Models\UserSubscriptionSchedule::where('status', 'ACTIVE')->whereBetween('expires_at', [now(), now()->addDays(3)])->count(),
            'total_monthly_revenue' => \App\Models\UserSubscriptionSchedule::where('status', 'ACTIVE')->where('expires_at', '>', now())->sum('monthly_price'),
        ];

        return view('admin.marketplace_subscription_plans.transport_schedules', compact('schedules', 'stats'));
    }
}
