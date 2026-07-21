<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineBookingSms extends Model
{
    protected $table = 'offline_booking_sms';

    protected $fillable = [
        'request_id',
        'provider_id',
        'provider_phone',
        'sms_code',
        'status',
        'expires_at'
    ];

    protected $dates = [
        'expires_at',
        'created_at',
        'updated_at'
    ];

    public function request()
    {
        return $this->belongsTo(UserRequests::class, 'request_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /** Alias for compatibility with the admin gateway view */
    public function userRequest()
    {
        return $this->belongsTo(UserRequests::class, 'request_id');
    }
}
