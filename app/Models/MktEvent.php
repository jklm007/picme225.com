<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktEvent extends Model
{
    protected $table = 'mkt_events';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\Models\MarketplaceListing::class, 'listable');
    }
}
