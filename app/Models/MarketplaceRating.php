<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceRating extends Model
{
    protected $fillable = [
        'user_id', 'seller_id', 'listing_id', 'rating', 'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function listing()
    {
        return $this->belongsTo(MarketplaceListing::class, 'listing_id');
    }
}
