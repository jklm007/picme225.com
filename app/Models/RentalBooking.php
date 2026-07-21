<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalBooking extends Model
{
    protected $fillable = [
        'listing_id', 'user_id', 'start_at', 'end_at',
        'total_price', 'deposit_amount', 'delivery_price',
        'commission_amount', 'owner_amount',
        'pickup_type', 'delivery_address', 'delivery_latitude', 'delivery_longitude',
        'status', 'escrow_status', 'deposit_status',
        'vehicle_condition_start', 'vehicle_condition_end', 'qr_code',
        'has_driver', 'driver_total_price',
    ];

    protected $casts = [
        'start_at'          => 'datetime',
        'end_at'            => 'datetime',
        'total_price'       => 'decimal:2',
        'deposit_amount'    => 'decimal:2',
        'delivery_price'    => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'owner_amount'      => 'decimal:2',
        'has_driver'        => 'boolean',
        'driver_total_price'=> 'decimal:2',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketplaceListing::class, 'listing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
