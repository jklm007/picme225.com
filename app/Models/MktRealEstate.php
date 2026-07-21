<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktRealEstate extends Model
{
    protected $table = 'mkt_real_estates';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\Models\MarketplaceListing::class, 'listable');
    }
}
