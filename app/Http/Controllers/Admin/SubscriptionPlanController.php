<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Service;
use App\Models\SubscriptionPlanServiceCommission;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     */
    public function index()
    {
        $plans = SubscriptionPlan::where('target', 'provider')->orderBy('priority', 'DESC')->get();
        
        // Statistiques par plan
        foreach ($plans as $plan) {
            $plan->active_subscribers = \App\Models\Provider::where('subscription_plan_id', $plan->id)
                ->where('subscription_expires_at', '>', now())
                ->count();
        }
        
        return view('admin.subscription_plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        $services = Service::all();
        return view('admin.subscription_plans.create', compact('services'));
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:subscription_plans',
            'price' => 'required|numeric|min:0',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0|max:100',
            'insurance_included' => 'required|boolean',
            'staking_bonus_percentage' => 'nullable|numeric|min:0|max:100',
            'max_categories' => 'required|integer|min:1',
        ]);

        $plan = SubscriptionPlan::create([
            'name' => strtoupper($request->name),
            'target' => 'provider',
            'price' => $request->price,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'priority' => $request->priority,
            'insurance_included' => $request->insurance_included,
            'staking_bonus_percentage' => $request->staking_bonus_percentage ?? 0,
            'max_categories' => $request->max_categories ?? 1,
            'status' => 1,
        ]);

        // Sauvegarder les commissions par service
        if ($request->has('service_commissions')) {
            foreach ($request->service_commissions as $serviceId => $commData) {
                if (isset($commData['value']) && $commData['value'] !== '') {
                    SubscriptionPlanServiceCommission::create([
                        'subscription_plan_id' => $plan->id,
                        'service_id' => $serviceId,
                        'commission_type' => $commData['type'],
                        'commission_value' => $commData['value'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.subscription-plans.index')
            ->with('flash_success', 'Plan d\'abonnement créé avec succès');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit($id)
    {
        $plan = SubscriptionPlan::with('serviceCommissions')->findOrFail($id);
        $services = Service::all();
        
        // Indexer les commissions existantes par serviceId
        $planCommissions = $plan->serviceCommissions->keyBy('service_id');
        
        return view('admin.subscription_plans.edit', compact('plan', 'services', 'planCommissions'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:subscription_plans,name,' . $id,
            'price' => 'required|numeric|min:0',
            'commission_type' => 'required|in:fixed,percentage',
            'commission_value' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0|max:100',
            'insurance_included' => 'required|boolean',
            'staking_bonus_percentage' => 'nullable|numeric|min:0|max:100',
            'max_categories' => 'required|integer|min:1',
            'status' => 'required|boolean',
        ]);

        $plan->update([
            'name' => strtoupper($request->name),
            'price' => $request->price,
            'commission_type' => $request->commission_type,
            'commission_value' => $request->commission_value,
            'priority' => $request->priority,
            'insurance_included' => $request->insurance_included,
            'staking_bonus_percentage' => $request->staking_bonus_percentage ?? 0,
            'max_categories' => $request->max_categories ?? 1,
            'status' => $request->status ?? 0,
        ]);

        // Mettre à jour les commissions par service
        if ($request->has('service_commissions')) {
            // Optionnel : Nettoyer les anciennes ? Ou faire un updateOrCreate
            SubscriptionPlanServiceCommission::where('subscription_plan_id', $plan->id)->delete();
            
            foreach ($request->service_commissions as $serviceId => $commData) {
                if (isset($commData['value']) && $commData['value'] !== '') {
                    SubscriptionPlanServiceCommission::create([
                        'subscription_plan_id' => $plan->id,
                        'service_id' => $serviceId,
                        'commission_type' => $commData['type'],
                        'commission_value' => $commData['value'],
                    ]);
                }
            }
        }

        return redirect()->route('admin.subscription-plans.index')
            ->with('flash_success', 'Plan mis à jour avec succès');
    }

    /**
     * Remove the specified plan (soft delete - désactivation).
     */
    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        
        // Vérifier s'il y a des abonnés actifs
        $activeSubscribers = \App\Models\Provider::where('subscription_plan_id', $plan->id)
            ->where('subscription_expires_at', '>', now())
            ->count();
        
        if ($activeSubscribers > 0) {
            return redirect()->back()->with('flash_error', 
                "Impossible de supprimer ce plan : {$activeSubscribers} chauffeur(s) actif(s)");
        }
        
        // Désactiver au lieu de supprimer
        $plan->update(['status' => 0]);
        
        return redirect()->route('admin.subscription-plans.index')
            ->with('flash_success', 'Plan désactivé avec succès');
    }
    
    /**
     * Toggle plan status.
     */
    public function toggleStatus($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['status' => !$plan->status]);
        
        $status = $plan->status ? 'activé' : 'désactivé';
        return redirect()->back()->with('flash_success', "Plan {$status} avec succès");
    }
}
