<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripMatch extends Model
{
    protected $fillable = [
        'trip_id', 'intention_id', 'score', 'status'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function intention()
    {
        return $this->belongsTo(Intention::class);
    }
}
