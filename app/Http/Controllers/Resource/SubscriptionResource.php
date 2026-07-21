<?php

namespace App\Http\Controllers\Resource;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanServiceType;
use App\Models\ServiceType;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class SubscriptionResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = SubscriptionPlan::all();
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $service_types = ServiceType::all();
        $services = Service::all();
        return view('admin.subscriptions.create', compact('service_types', 'services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric',
            'fixed_fee' => 'required|numeric',
            'priority' => 'required|integer',
            'staking_bonus_percentage' => 'required|numeric',
            'period' => 'required|in:DAILY,WEEKLY,MONTHLY',
            'max_categories' => 'required|integer',
            'service_id' => 'nullable|integer',
        ]);

        try {
            $data = $request->all();
            $data['insurance_included'] = $request->has('insurance_included') ? 1 : 0;
            if (empty($data['service_id'])) {
                $data['service_id'] = null;
            }
            $subscription = SubscriptionPlan::create($data);

            if ($request->has('service_commissions')) {
                foreach ($request->service_commissions as $service_type_id => $data_comm) {
                    if (!empty($data_comm['value'])) {
                        SubscriptionPlanServiceType::create([
                            'subscription_plan_id' => $subscription->id,
                            'service_type_id' => $service_type_id,
                            'commission_type' => $data_comm['type'],
                            'commission_value' => $data_comm['value'],
                        ]);
                    }
                }
            }

            return redirect()->route('admin.subscription.index')->with('flash_success', 'Subscription Plan Created Successfully');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Error creating subscription: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $subscription = SubscriptionPlan::with('serviceTypes')->findOrFail($id);
            $service_types = ServiceType::all();
            $services = Service::all();
            $commissions = $subscription->serviceTypes->pluck('commission_value', 'service_type_id')->all();
            $commission_types = $subscription->serviceTypes->pluck('commission_type', 'service_type_id')->all();
            
            return view('admin.subscriptions.edit', compact('subscription', 'service_types', 'services', 'commissions', 'commission_types'));
        } catch (Exception $e) {
            return back()->with('flash_error', 'Subscription not found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'price' => 'required|numeric',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric',
            'fixed_fee' => 'required|numeric',
            'priority' => 'required|integer',
            'staking_bonus_percentage' => 'required|numeric',
            'period' => 'required|in:DAILY,WEEKLY,MONTHLY',
            'max_categories' => 'required|integer',
            'service_id' => 'nullable|integer',
        ]);

        try {
            $subscription = SubscriptionPlan::findOrFail($id);
            $data = $request->all();
            $data['insurance_included'] = $request->has('insurance_included') ? 1 : 0;
            if (empty($data['service_id'])) {
                $data['service_id'] = null;
            }
            $subscription->update($data);

            // Update service-specific commissions
            SubscriptionPlanServiceType::where('subscription_plan_id', $id)->delete();
            if ($request->has('service_commissions')) {
                foreach ($request->service_commissions as $service_type_id => $data_comm) {
                    if (!empty($data_comm['value'])) {
                        SubscriptionPlanServiceType::create([
                            'subscription_plan_id' => $subscription->id,
                            'service_type_id' => $service_type_id,
                            'commission_type' => $data_comm['type'],
                            'commission_value' => $data_comm['value'],
                        ]);
                    }
                }
            }

            return redirect()->route('admin.subscription.index')->with('flash_success', 'Subscription Plan Updated Successfully');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Error updating subscription: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            SubscriptionPlan::findOrFail($id)->delete();
            return back()->with('flash_success', 'Subscription Plan deleted successfully');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Error deleting subscription');
        }
    }
}
