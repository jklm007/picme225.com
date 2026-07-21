<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaoVote extends Model
{
    protected $fillable = [
        'proposal_id',
        'user_id',
        'voter_type',
        'user_wallet_address',
        'vote',
        'token_amount',
        'transaction_hash',
        'status',
        'votes_weight',
    ];

    protected $casts = [
        'token_amount' => 'decimal:8',
    ];

    /**
     * Relation avec la proposition
     */
    public function proposal()
    {
        return $this->belongsTo(DaoProposal::class, 'proposal_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        if ($this->voter_type === 'PROVIDER') {
            return $this->belongsTo(Provider::class, 'user_id');
        }
        return $this->belongsTo(User::class, 'user_id');
    }
}

