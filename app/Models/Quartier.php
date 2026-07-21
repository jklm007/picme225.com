<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'quartiers';

    protected $fillable = [
        'commune_id', 'nom_quartier', 'latitude', 'longitude', 'statut'
    ];

    protected $guarded = ['zone_geo'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function pdpStops()
    {
        return $this->hasMany(PdpStop::class);
    }

    /**
     * Scope pour trouver le quartier qui contient un point GPS
     */
    public function scopeContainsPoint($query, $lat, $lng)
    {
        return $query->whereRaw("ST_Contains(zone_geo::geometry, ST_SetSRID(ST_Point(?, ?), 4326))", [$lng, $lat]);
    }
}
