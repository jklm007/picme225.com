<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaoProposal extends Model
{
    protected $fillable = [
        'blockchain_proposal_id',
        'user_id',
        'creator_type',
        'type',
        'title',
        'description',
        'execution_data',
        'status',
        'start_time',
        'end_time',
        'votes_for',
        'votes_against',
        'votes_abstain',
        'executed',
        'executed_at',
    ];

    protected $casts = [
        'execution_data' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'executed_at' => 'datetime',
        'votes_for' => 'integer',
        'votes_against' => 'integer',
        'votes_abstain' => 'integer',
        'executed' => 'boolean',
    ];

    /**
     * Relation avec l'utilisateur qui a créé la proposition
     */
    public function proposer()
    {
        if ($this->creator_type === 'PROVIDER') {
            return $this->belongsTo(Provider::class, 'user_id');
        }
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec les votes
     */
    public function votes()
    {
        return $this->hasMany(DaoVote::class, 'proposal_id');
    }

    /**
     * Vérifier si la proposition est active
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE' && now() < $this->end_time;
    }

    /**
     * Vérifier si la proposition a atteint le quorum
     */
    public function hasReachedQuorum($quorum)
    {
        $totalVotes = $this->votes_for + $this->votes_against + $this->votes_abstain;
        return $totalVotes >= $quorum;
    }
}

