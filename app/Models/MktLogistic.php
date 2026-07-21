<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktLogistic extends Model
{
    protected $table = 'mkt_logistics';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\Models\MarketplaceListing::class, 'listable');
    }
}
