<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intention extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'origin_name', 'destination_name', 'origin_lat', 'origin_lng',
        'destination_lat', 'destination_lng', 'earliest_departure', 'latest_departure',
        'seats_needed', 'budget_max', 'description', 'status', 'pdp_route_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matches()
    {
        return $this->hasMany(TripMatch::class);
    }
}
