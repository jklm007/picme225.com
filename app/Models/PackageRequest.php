<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageRequest extends Model
{
    protected $fillable = [
        'user_id',
        'sender_name',
        'sender_phone',
        'recipient_name',
        'recipient_phone',
        'pickup_station_id',
        's_address',
        's_latitude',
        's_longitude',
        'dropoff_station_id',
        'd_address',
        'd_latitude',
        'd_longitude',
        'type',
        'interurban_company_id',
        'pdp_route_id',
        'needs_collection',
        'collection_request_id',
        'description',
        'package_type',
        'size_category',
        'weight',
        'picture',
        'status',
        'tracking_code',
        'otp_pickup',
        'distance',
        'price',
        'payment_mode',
        'paid',
        'provider_id'
    ];

    /**
     * Get the user who sent the package.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider (driver) for instant delivery.
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the pickup station.
     */
    public function pickup_station()
    {
        return $this->belongsTo(PdpStop::class, 'pickup_station_id');
    }

    /**
     * Get the dropoff station.
     */
    public function dropoff_station()
    {
        return $this->belongsTo(PdpStop::class, 'dropoff_station_id');
    }

    /**
     * Get the company transporting the package.
     */
    public function company()
    {
        return $this->belongsTo(InterurbanCompany::class, 'interurban_company_id');
    }

    /**
     * Get the collection ride (if any).
     */
    public function collection_ride()
    {
        return $this->belongsTo(UserRequests::class, 'collection_request_id');
    }
}
