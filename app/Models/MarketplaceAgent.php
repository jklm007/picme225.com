<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceAgent extends Model
{
    protected $fillable = [
        'listing_id',
        'user_id'
    ];

    public function listing()
    {
        return $this->belongsTo(\App\Models\MarketplaceListing::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
