<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdpStop extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'pdp_stops';

    protected $fillable = [
        'nom_arret', 'type_arret', 'commune_id', 'quartier_id',
        'adresse', 'description', 'latitude', 'longitude',
        'rayon_validation_metre', 'precision_gps', 'source_coordonnees',
        'photon_place_id', 'photon_raw_data', 'ors_verified',
        'statut_validation', 'confidence_score'
    ];

    protected $casts = [
        'photon_raw_data' => 'array',
        'ors_verified' => 'boolean',
    ];

    protected $guarded = ['location'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }

    /**
     * Set location directly using ST_MakePoint
     */
    public function setLocationAttribute($lat, $lng)
    {
        $this->attributes['latitude'] = $lat;
        $this->attributes['longitude'] = $lng;
        // La colonne s'appelle 'location'
        $this->attributes['location'] = \DB::raw("ST_SetSRID(ST_Point({$lng}, {$lat}), 4326)");
    }

    /**
     * Scope pour trouver les arrêts dans un rayon de X km
     */
    public function scopeWithinRadius($query, $lat, $lng, $radiusKm = 2)
    {
        // 1 degré = ~111 km, donc radius en degrés (approximation très rapide pour la box GIST)
        $radiusDegrees = $radiusKm / 111.0;
        
        return $query->whereRaw("ST_DWithin(location, ST_SetSRID(ST_Point(?, ?), 4326), ?)", [$lng, $lat, $radiusDegrees])
                     ->orderByRaw("ST_Distance(location, ST_SetSRID(ST_Point(?, ?), 4326)) ASC", [$lng, $lat]);
    }
}
