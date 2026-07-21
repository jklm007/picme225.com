<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PdpRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'interurban_company_id',
        'status',
        'created_by_user_id',
        'description',
        'total_votes',
        'positive_votes',
        'negative_votes',
        'base_price_per_segment',
        'detour_price_per_km',
        'max_detour_communal',
        'max_detour_intercommunal',
        'is_active',
        'is_intercommunal',
        'is_communal',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_intercommunal' => 'boolean',
        'is_communal' => 'boolean',
        'base_price_per_segment' => 'decimal:2',
        'detour_price_per_km' => 'decimal:2',
    ];

    /**
     * Relation avec l'utilisateur qui a créé l'itinéraire
     */
    public function creator()
    {
        return $this->belongsTo(User::class , 'created_by_user_id');
    }

    /**
     * Relation avec les arrêts de l'itinéraire
     */
    public function stops()
    {
        return $this->belongsToMany(PdpStop::class, 'pdp_route_stops', 'pdp_route_id', 'pdp_stop_id')
                    ->withPivot('order', 'price')
                    ->orderBy('pdp_route_stops.order');
    }

    /**
     * Relation avec les segments de l'itinéraire
     */
    public function segments()
    {
        return $this->hasMany(PdpRouteSegment::class , 'pdp_route_id')->orderBy('order');
    }

    /**
     * Relation avec les votes
     */
    public function votes()
    {
        return $this->hasMany(PdpRouteVote::class , 'pdp_route_id');
    }

    /**
     * Relation avec les trajets actifs
     */
    public function activeRides()
    {
        return $this->hasMany(ActiveSharedRide::class , 'pdp_route_id');
    }

    /**
     * Vérifier si l'itinéraire est approuvé
     */
    public function isApproved()
    {
        return $this->status === 'APPROVED';
    }

    /**
     * Relation avec la compagnie interurbaine
     */
    public function company()
    {
        return $this->belongsTo(InterurbanCompany::class , 'interurban_company_id');
    }
}
