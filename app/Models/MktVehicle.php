<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktVehicle extends Model
{
    protected $table = 'mkt_vehicles';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\Models\MarketplaceListing::class, 'listable');
    }
}
