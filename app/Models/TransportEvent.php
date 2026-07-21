<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'service_type_id',
        'pdp_route_id',
        'title',
        'description',
        's_address',
        's_latitude',
        's_longitude',
        'd_address',
        'd_latitude',
        'd_longitude',
        'departure_time',
        'price',
        'total_seats',
        'available_seats',
        'status',
    ];

    /**
     * Provider who created the event (Driver / Fleet Owner)
     */
    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    /**
     * Vehicle Type / Service Level
     */
    public function serviceType()
    {
        return $this->belongsTo('App\Models\ServiceType');
    }

    /**
     * Corridor linked
     */
    public function route()
    {
        return $this->belongsTo('App\Models\PdpRoute', 'pdp_route_id');
    }

    /**
     * Social Post representing this event in the feed
     */
    public function post()
    {
        // Using trip_id and type = 'SOCIAL' and category = 'TICKET'
        return $this->hasOne('App\Models\Post', 'trip_id')->where('category', 'TICKET');
    }

    /**
     * Tickets bought for this event
     */
    public function tickets()
    {
        return $this->hasMany('App\Models\TransportTicket', 'transport_event_id');
    }

    /**
     * Passes (Ticket Types) available for this event
     */
    public function passes()
    {
        return $this->hasMany('App\Models\EventPassType', 'event_id');
    }
}
