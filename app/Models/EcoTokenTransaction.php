<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoTokenTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_address',
        'type',
        'amount',
        'transaction_hash',
        'status',
        'reference_type',
        'reference_id',
        'metadata',
        'block_number',
        'confirmations',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'metadata' => 'array',
        'confirmations' => 'integer',
        'block_number' => 'integer',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation polymorphique avec l'entité référencée
     */
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}

