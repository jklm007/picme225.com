<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    protected $fillable = [
        'trip_id', 'user_id', 'seats_booked', 'price', 'handshake_code',
        'status', 'payment_status', 'payment_mode', 'boarded_at', 'completed_at'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
