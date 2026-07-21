<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'origin_name', 'destination_name', 'origin_lat', 'origin_lng',
        'destination_lat', 'destination_lng', 'departure_time', 'seats_available',
        'price', 'description', 'status', 'pdp_route_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function corridor()
    {
        return $this->belongsTo(PdpRoute::class, 'pdp_route_id');
    }

    public function matches()
    {
        return $this->hasMany(TripMatch::class);
    }
}
