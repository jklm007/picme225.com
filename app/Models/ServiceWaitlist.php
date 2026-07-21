<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceWaitlist extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_waitlist';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'service_id',
        'service_type_id',
        'latitude',
        'longitude',
        'zone',
        'position',
        'status',
        'notified_at',
        'subscription_plan_id',
        'preferred_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'notified_at' => 'datetime',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    /**
     * The user on the waitlist.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * The service this waitlist entry belongs to.
     */
    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    /**
     * The subscription plan associated with this waitlist entry.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(\App\Models\SubscriptionPlan::class);
    }
}
