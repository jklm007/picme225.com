<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideBooking extends Model
{
    protected $fillable = [
        'active_shared_ride_id',
        'user_id',
        'start_stop_id',
        'end_stop_id',
        'seats_booked',
        'price',
        'detour_distance',
        'detour_price',
        'status',
        'handshake_code',
        'payment_mode',
        'paid',
        'use_wallet',
        'boarded_at',
        'completed_at',
        'cancellation_reason',
        'commission_amount',
        'driver_amount',
        'escrow_status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'detour_distance' => 'decimal:2',
        'detour_price' => 'decimal:2',
        'seats_booked' => 'integer',
        'paid' => 'boolean',
        'use_wallet' => 'boolean',
        'boarded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relation avec le trajet actif
     */
    public function activeRide()
    {
        return $this->belongsTo(ActiveSharedRide::class, 'active_shared_ride_id');
    }

    /**
     * Alias pour activeRide() — utilisé par SocialTransportController::verifyHandshake/verifyArrivalHandshake
     */
    public function activeSharedRide()
    {
        return $this->activeRide();
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec l'arrêt de départ
     */
    public function startStop()
    {
        return $this->belongsTo(PdpStop::class, 'start_stop_id');
    }

    /**
     * Relation avec l'arrêt d'arrivée
     */
    public function endStop()
    {
        return $this->belongsTo(PdpStop::class, 'end_stop_id');
    }

    /**
     * Calculer le prix total (prix de base + détour)
     */
    public function getTotalPriceAttribute()
    {
        return $this->price + $this->detour_price;
    }
}

