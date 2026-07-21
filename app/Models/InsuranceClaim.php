<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceClaim extends Model
{
    protected $fillable = [
        'provider_id',
        'amount_requested',
        'amount_approved',
        'incident_description',
        'incident_location',
        'incident_date',
        'document_url',
        'status',
        'admin_comment',
    ];

    protected $dates = [
        'incident_date',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
