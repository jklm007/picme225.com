<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'communes';
    
    protected $fillable = [
        'pays', 'ville', 'commune', 'code_commune', 
        'latitude_centre', 'longitude_centre', 'statut'
    ];
    
    // Le polygone sera géré via des requêtes raw de PostGIS pour simplifier l'insertion
    protected $guarded = ['polygone_zone'];

    public function quartiers()
    {
        return $this->hasMany(Quartier::class);
    }

    public function pdpStops()
    {
        return $this->hasMany(PdpStop::class);
    }

    /**
     * Scope pour trouver la commune qui contient un point GPS donné
     * Utilisant ST_Contains de PostGIS
     */
    public function scopeContainsPoint($query, $lat, $lng)
    {
        return $query->whereRaw("ST_Contains(polygone_zone::geometry, ST_SetSRID(ST_Point(?, ?), 4326))", [$lng, $lat]);
    }
}
