<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileMoneyTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'provider',
        'amount',
        'phone_number',
        'transaction_id',
        'reference',
        'type',
        'status',
        'provider_response',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'provider_response' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec le chauffeur
     */
    public function chauffer()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}

