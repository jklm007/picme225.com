<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        's_address',
        's_lat',
        's_lng',
        'd_address',
        'd_lat',
        'd_lng',
        'waypoints',
        'pickup_time',
        'return_time',
        'active_days',
        'monthly_price',
        // OSRM routing data (replaces Haversine)
        'distance_km',
        'duration_mins',
        // Subscription validity
        'expires_at',
        'payment_mode',
        'notes',
        'status',
    ];

    protected $casts = [
        'active_days'   => 'array',
        'waypoints'     => 'array',
        'expires_at'    => 'datetime',
        'distance_km'   => 'float',
        'duration_mins' => 'integer',
        'monthly_price' => 'float',
    ];

    // ─── Relations ───────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function serviceType()
    {
        return $this->belongsTo('App\Models\ServiceType', 'service_id');
    }

    // ─── Business Logic ──────────────────────────────────────────────────────

    /**
     * Whether this schedule is currently within its paid validity period.
     */
    public function isValid(): bool
    {
        return $this->status === 'ACTIVE'
            && $this->expires_at
            && Carbon::now()->lt($this->expires_at);
    }

    /**
     * Days remaining in the current subscription period.
     */
    public function daysRemaining(): int
    {
        if (! $this->expires_at) return 0;
        $diff = Carbon::now()->diffInDays($this->expires_at, false);
        return max(0, (int) $diff);
    }
}
