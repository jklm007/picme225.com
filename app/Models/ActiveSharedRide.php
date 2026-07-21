<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveSharedRide extends Model
{
    protected $fillable = [
        'pdp_route_id',
        'provider_id',
        'service_type_id',
        'vehicle_id',
        'status',
        'available_seats',
        'total_seats',
        'current_latitude',
        'current_longitude',
        'destination_latitude',
        'destination_longitude',
        'next_stop_id',
        'current_stop_id',
        'price_per_seat',
        'started_at',
        'ended_at',
        'last_position_update',
    ];

    protected $casts = [
        'available_seats' => 'integer',
        'total_seats' => 'integer',
        'price_per_seat' => 'decimal:2',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_position_update' => 'datetime',
    ];

    /**
     * Relation avec l'itinéraire
     */
    public function route()
    {
        return $this->belongsTo(PdpRoute::class, 'pdp_route_id');
    }

    /**
     * Relation avec le provider (chauffeur)
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Relation avec le prochain arrêt
     */
    public function nextStop()
    {
        return $this->belongsTo(PdpStop::class, 'next_stop_id');
    }

    /**
     * Relation avec l'arrêt actuel
     */
    public function currentStop()
    {
        return $this->belongsTo(PdpStop::class, 'current_stop_id');
    }

    /**
     * Relation avec les réservations
     */
    public function bookings()
    {
        return $this->hasMany(RideBooking::class, 'active_shared_ride_id');
    }

    /**
     * Relation avec le service type
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    /**
     * Vérifier si le trajet est actif
     */
    public function isActive()
    {
        return $this->status === 'EN_ROUTE';
    }

    /**
     * Vérifier s'il y a des places disponibles
     */
    public function hasAvailableSeats($seatsNeeded = 1)
    {
        return $this->available_seats >= $seatsNeeded;
    }
}

