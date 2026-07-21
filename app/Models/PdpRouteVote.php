<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdpRouteVote extends Model
{
    protected $fillable = [
        'pdp_route_id',
        'user_id',
        'vote',
        'comment',
    ];

    /**
     * Relation avec l'itinéraire
     */
    public function route()
    {
        return $this->belongsTo(PdpRoute::class, 'pdp_route_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

