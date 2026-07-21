<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MktService extends Model
{
    protected $table = 'mkt_services';
    protected $guarded = [];

    public function listing()
    {
        return $this->morphOne(\App\Models\MarketplaceListing::class, 'listable');
    }
}
