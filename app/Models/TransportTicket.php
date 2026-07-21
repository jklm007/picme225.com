<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportTicket extends Model
{
    protected $fillable = [
        'listing_id',
        'event_pass_type_id',
        'transport_event_id',
        'user_id',
        'qr_code',
        'totp_secret',
        'seats_booked',
        'total_price',
        'payment_status',
        'payment_mode',
        'status',
        'metadata',
    ];

    public function pass()
    {
        return $this->belongsTo('App\Models\EventPassType', 'event_pass_type_id');
    }

    public function listing()
    {
        return $this->belongsTo('App\Models\MarketplaceListing', 'listing_id');
    }

    public function event()
    {
        return $this->belongsTo('App\Models\TransportEvent', 'transport_event_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User'); // Passenger
    }
}
