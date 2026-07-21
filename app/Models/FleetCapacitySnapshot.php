<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FleetCapacitySnapshot extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fleet_capacity_snapshots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'service_id',
        'zone',
        'online_providers',
        'active_requests',
        'utilization_rate',
        'avg_wait_time_min',
        'threshold_met',
        'snapped_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'service_id'       => 'integer',
        'online_providers' => 'integer',
        'active_requests'  => 'integer',
        'utilization_rate' => 'float',
        'avg_wait_time_min'=> 'float',
        'threshold_met'    => 'boolean',
        'snapped_at'       => 'datetime',
    ];
}
