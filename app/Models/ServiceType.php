<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'provider_name',
        'image',
        'price',
        'fixed',
        'description',
        'status',
        'minute',
        'hour',
        'distance',
        'calculator',
        'capacity',
        'rental_amount',
        'ambulance',
        'day',
        'outstation_price',
        'sharing_type',
        'free_km_per_passenger',
        'price_per_segment',
        'price_per_km',
        'max_detour_communal',
        'max_detour_intercommunal',
        // Nouveaux champs pour le partage
        'max_detour',
        'max_waiting_time',
        'detour_price_per_km',
        // Restriction géographique
        'commune',
        'communes',
        'is_intercommunal',
        'is_interregional',
        'is_intercity',
        'requires_feeder_ride',
        'can_act_as_feeder',
        'feeder_trigger_radius',
        'commission_percentage',
        'eco_discount_percent',
        'is_communal',
        // Antigravity: Couverture de zone pour le moteur de filtrage intelligent
        'zone_coverage',
        'max_distance',
        'allowed_variants',
        'arret_discount_percent',
        'interurban_company_id',
        'ac_price',
        'service_class',
        'km_per_segment',
        'allow_without_driver',
        'shared_capacity',
        // Nouveaux champs manquants
        'is_taxable',
        'requires_pro_subscription',
        'is_shared',
        'shared_type',
        'shared_communal_base',
        'shared_intercommunal_base',
        'shared_intercommunal_per_km',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Antigravity: Append full image_url to JSON output.
     */
    protected $appends = ['image_url'];


    /**
     * Antigravity: Accessor — returns the full public URL for the image field.
     * This ensures mobile apps always receive a complete URL.
     */
    public function getImageUrlAttribute()
    {
        $image = $this->image;

        // No image — return a placeholder
        if (empty($image)) {
            return asset('images/default_vehicle.png');
        }

        // Already a full URL (R2, Cloudflare, CDN) — return as-is
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $parsed = parse_url($image);
            if (isset($parsed['host']) && ($parsed['host'] === '127.0.0.1' || $parsed['host'] === 'localhost')) {
                $image = ltrim($parsed['path'], '/');
            } else {
                return $image; // External / R2 URL — return directly
            }
        }

        $path = ltrim($image, '/');

        // Known prefixes that include the folder already
        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'uploads/') ||
            str_starts_with($path, 'asset/') || str_starts_with($path, 'img/')) {
            return url($path);
        }

        // Relative path like "service/taxi.png" — prepend storage/ or use img()
        return img($path);
    }

    protected $casts = [
        'free_km_per_passenger' => 'integer',
        'price_per_segment' => 'float',
        'price_per_km' => 'float',
        'max_detour_communal' => 'integer',
        'max_detour_intercommunal' => 'integer',
        // Nouveaux casts
        'max_detour' => 'float',
        'max_waiting_time' => 'integer',
        'detour_price_per_km' => 'float',
        'communes' => 'array', // JSON to array
        'is_intercommunal' => 'boolean',
        'is_interregional' => 'boolean',
        'is_intercity' => 'boolean',
        'requires_feeder_ride' => 'boolean',
        'can_act_as_feeder' => 'boolean',
        'is_communal' => 'boolean',
        // Antigravity: zone_coverage — COMMUNAL | INTERCOMMUNAL | TOUTE_ZONE
        // Valeur calculée par la migration 2026_06_18_000001
        // (pas de cast spécial, c'est une string ENUM)
        'max_distance' => 'float',
        'allowed_variants' => 'array',
        'arret_discount_percent' => 'float',
        'allow_without_driver' => 'boolean',
        'shared_capacity' => 'integer',
        'is_taxable' => 'boolean',
        'requires_pro_subscription' => 'boolean',
        'is_shared' => 'boolean',
        'shared_communal_base' => 'float',
        'shared_intercommunal_base' => 'float',
        'shared_intercommunal_per_km' => 'float',
    ];

    // =========================================================================
    // SCOPES — Moteur de filtrage intelligent par zone géographique
    // =========================================================================

    /**
     * Antigravity: Services communaux uniquement (intra-commune).
     * Exemple : Taxi local, Woro-woro communal.
     */
    public function scopeCommunal($query)
    {
        return $query->where('zone_coverage', 'COMMUNAL');
    }

    /**
     * Antigravity: Services intercommunaux (entre communes, mais pas universel).
     * Exemple : Taxi Inter-communal arret_pdp.
     */
    public function scopeIntercommunal($query)
    {
        return $query->where('zone_coverage', 'INTERCOMMUNAL');
    }

    /**
     * Antigravity: Services universels (toutes zones — VTC, Voyage inter-régional).
     * Définition : is_communal=false, is_intercommunal=true, is_interregional=true.
     */
    public function scopeUniversal($query)
    {
        return $query->where('zone_coverage', 'TOUTE_ZONE');
    }

    /**
     * Antigravity: Filtre les ServiceType selon le mode de trajet détecté.
     *
     * @param  string  $tripMode  'same_commune' | 'different_communes' | 'unknown'
     *
     * Règles :
     *  - same_commune      → COMMUNAL + INTERCOMMUNAL + TOUTE_ZONE (tout afficher)
     *  - different_communes → INTERCOMMUNAL + TOUTE_ZONE seulement
     *  - unknown           → tout afficher (pas assez d'info)
     */
    public function scopeForTripMode($query, string $tripMode)
    {
        if ($tripMode === 'different_communes') {
            return $query->whereIn('zone_coverage', ['INTERCOMMUNAL', 'TOUTE_ZONE']);
        }
        // same_commune ou unknown → aucun filtre zone
        return $query;
    }

    /**
     * Scope for Feeder Service Types (e.g. Woro, Taxi).
     */
    public function scopeFeeders($query)
    {
        return $query->where('can_act_as_feeder', true);
    }

    public function kmHourPackagePrices()
    {
        // Renommer la relation pour éviter confusion avec un éventuel champ "price" sur ServiceType
        return $this->hasMany(KmHourServiceTypePrice::class);
    }

    // Pourrait être utile pour lister les forfaits disponibles pour ce ServiceType avec leurs prix
    public function getAvailableKmHourPackagesWithPrices()
    {
        return KmHour::whereHas('serviceTypePrices', function ($query) {
            $query->where('service_type_id', $this->id);
        })->with([
            'serviceTypePrices' => function ($query) {
            $query->where('service_type_id', $this->id);
        }
        ])->get()->map(function ($kmHour) {
            $priceEntry = $kmHour->serviceTypePrices->first();
            return [
                'id' => $kmHour->id,
                'kilometer' => $kmHour->kilometer,
                'hour' => $kmHour->hour,
                'price' => $priceEntry ? $priceEntry->price : null,
            ];
        });
    }



    public function service()
    {
        return $this->hasmany('App\Models\ServiceTypeRental');
    }


    public function services()
    {
        return $this->belongsToMany('App\Models\Service', 'service_service_type', 'service_type_id', 'service_id')
            ->withPivot('fixed', 'price', 'minute', 'hour', 'distance', 'day', 'calculator', 'description', 'status', 'ambulance', 'rental_amount', 'outstation_price');
    }

    /**
     * Get the company that provides this service type.
     */
    public function company()
    {
        return $this->belongsTo(InterurbanCompany::class , 'interurban_company_id');
    }
}
