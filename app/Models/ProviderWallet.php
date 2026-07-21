<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderWallet extends Model
{
    protected $fillable = [
        'provider_id',
        'amount',
        'transaction_id',
        'transaction_desc',
        'type',
        'balance',
    ];

    /**
     * Relation avec le chauffeur
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
