<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdpRouteSegment extends Model
{
    protected $fillable = [
        'pdp_route_id',
        'service_type_id',
        'allowed_service_types',
        'from_stop_id',
        'to_stop_id',
        'order',
        'price',
        'distance_km',
        'commune',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'is_active' => 'boolean',
        'order' => 'integer',
        'allowed_service_types' => 'array',
    ];

    /**
     * Scope local pour filtrer par type de service (évolutif JSON / Pivot)
     */
    public function scopeForServiceType($query, $serviceTypeId)
    {
        return $query->where(function($q) use ($serviceTypeId) {
            $q->whereJsonContains('pdp_route_segments.allowed_service_types', (int)$serviceTypeId)
              ->orWhere('pdp_route_segments.service_type_id', $serviceTypeId)
              ->orWhereNull('pdp_route_segments.allowed_service_types');
        });
    }

    /**
     * Relation avec l'itinéraire
     */
    public function route()
    {
        return $this->belongsTo(PdpRoute::class, 'pdp_route_id');
    }

    /**
     * Relation avec l'arrêt de départ
     */
    public function fromStop()
    {
        return $this->belongsTo(PdpStop::class, 'from_stop_id');
    }

    /**
     * Relation avec l'arrêt d'arrivée
     */
    public function toStop()
    {
        return $this->belongsTo(PdpStop::class, 'to_stop_id');
    }

    /**
     * Relation avec le type de service
     */
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}

