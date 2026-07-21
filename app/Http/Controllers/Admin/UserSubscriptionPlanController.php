<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class UserSubscriptionPlanController extends Controller
{
    /**
     * Display a listing of user subscription plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::where('target', 'user')->orderBy('priority', 'DESC')->get();
        
        // Statistiques par plan pour les utilisateurs
        foreach ($plans as $plan) {
            $plan->active_subscribers = \App\Models\User::where('subscription_plan_id', $plan->id)
                ->where('subscription_expires_at', '>', now())
                ->count();
        }
        
        return view('admin.user_subscription_plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new user plan.
     */
    public function create()
    {
        return view('admin.user_subscription_plans.create');
    }

    /**
     * Store a newly created user plan.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:subscription_plans',
            'price' => 'required|numeric|min:0',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0|max:1000',
            'insurance_included' => 'required|boolean',
            'max_categories' => 'required|integer|min:1',
            'period' => 'required|in:DAILY,WEEKLY,MONTHLY,YEARLY',
        ]);

        SubscriptionPlan::create([
            'name' => strtoupper($request->name),
            'target' => 'user',
            'service_id' => null,
            'price' => $request->price,
            'period' => $request->period,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'priority' => $request->priority,
            'insurance_included' => $request->insurance_included,
            'max_categories' => $request->max_categories ?? 1,
            'status' => 1,
        ]);

        return redirect()->route('admin.user-subscription-plans.index')
            ->with('flash_success', 'Plan d\'abonnement utilisateur créé avec succès');
    }

    /**
     * Show the form for editing the specified user plan.
     */
    public function edit($id)
    {
        $plan = SubscriptionPlan::where('target', 'user')->findOrFail($id);
        return view('admin.user_subscription_plans.edit', compact('plan'));
    }

    /**
     * Update the specified user plan.
     */
    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::where('target', 'user')->findOrFail($id);
        
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $id,
            'price' => 'required|numeric|min:0',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0|max:1000',
            'insurance_included' => 'required|boolean',
            'max_categories' => 'required|integer|min:1',
            'period' => 'required|in:DAILY,WEEKLY,MONTHLY,YEARLY',
            'status' => 'required|boolean',
        ]);

        $plan->update([
            'name' => strtoupper($request->name),
            'price' => $request->price,
            'period' => $request->period,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'priority' => $request->priority,
            'insurance_included' => $request->insurance_included,
            'max_categories' => $request->max_categories ?? 1,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('admin.user-subscription-plans.index')
            ->with('flash_success', 'Plan mis à jour avec succès');
    }

    /**
     * Remove the specified user plan (soft delete - désactivation).
     */
    public function destroy($id)
    {
        $plan = SubscriptionPlan::where('target', 'user')->findOrFail($id);
        
        $activeSubscribers = \App\Models\User::where('subscription_plan_id', $plan->id)
            ->where('subscription_expires_at', '>', now())
            ->count();
        
        if ($activeSubscribers > 0) {
            return redirect()->back()->with('flash_error', 
                "Impossible de supprimer ce plan : {$activeSubscribers} passager(s) actif(s)");
        }
        
        $plan->update(['status' => 0]);
        
        return redirect()->route('admin.user-subscription-plans.index')
            ->with('flash_success', 'Plan désactivé avec succès');
    }
    
    /**
     * Toggle user plan status.
     */
    public function toggleStatus($id)
    {
        $plan = SubscriptionPlan::where('target', 'user')->findOrFail($id);
        $plan->update(['status' => !$plan->status]);
        
        $status = $plan->status ? 'activé' : 'désactivé';
        return redirect()->back()->with('flash_success', "Plan {$status} avec succès");
    }
}
