<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktProduct extends Model
{
    protected $table = 'mkt_products';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\Models\MarketplaceListing::class, 'listable');
    }
}
