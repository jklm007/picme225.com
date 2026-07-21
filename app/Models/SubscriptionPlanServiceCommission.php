<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanServiceCommission extends Model
{
    protected $fillable = [
        'subscription_plan_id',
        'service_id',
        'commission_type',
        'commission_value',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
