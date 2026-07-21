<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanServiceType extends Model
{
    protected $fillable = [
        'subscription_plan_id',
        'service_type_id',
        'commission_type',
        'commission_value',
    ];

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}
