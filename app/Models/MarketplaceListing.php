<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceListing extends Model
{
    use SoftDeletes;

    public $_virtual_attributes = [];

    protected $fillable = [
        'user_id', 'type', 'title', 'description', 'price', 'price_unit',
        'brand', 'model', 'year', 'color', 'plate_number',
        'cover_image', 'images', 'with_driver',
        'deposit_amount', 'delivery_price', 'home_delivery',
        'location_city', 'location_latitude', 'location_longitude',
        'pdp_route_id', 'status', 'category', 'metadata', 'driver_price', 'driving_policy',
        'owner_name', 'owner_phone', 'cleanup_prompt_at',
        'is_digital', 'digital_file_path', 'stock_quantity', 'pickup_address',
        'source', 'whatsapp_message_id', 'ai_confidence_score', 'sub_category'
    ];

    protected $appends = [
        'average_rating', 'media_url', 'available_actions',
        'brand', 'model', 'year', 'color', 'plate_number', 'with_driver', 'driver_price', 'driving_policy',
        'location_city', 'location_latitude', 'location_longitude', 'price_unit',
        'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id', 'pickup_address'
    ];

    protected $casts = [
        'images'           => 'array',
        'metadata'         => 'array',
        'with_driver'      => 'boolean',
        'home_delivery'    => 'boolean',
        'deposit_amount'   => 'decimal:2',
        'delivery_price'   => 'decimal:2',
        'price'            => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function corridor(): BelongsTo
    {
        return $this->belongsTo(PdpRoute::class, 'pdp_route_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RentalBooking::class, 'listing_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(MarketplaceRating::class, 'listing_id');
    }

    public function passes(): HasMany
    {
        return $this->hasMany(\App\Models\EventPassType::class, 'listing_id');
    }    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Models\TransportTicket::class, 'listing_id');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(\App\Models\MarketplaceAgent::class, 'listing_id');
    }

    public function listable()
    {
        return $this->morphTo();
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?: 0;
    }

    public function getMediaUrlAttribute()
    {
        if (!$this->cover_image) {
            // Fall back to first image in images array
            if (!empty($this->images) && is_array($this->images)) {
                $first = $this->images[0] ?? null;
                if (!$first) return null;
                // Already a full public URL (uploaded via WhatsApp job, S3, CDN) — return directly
                if (str_starts_with($first, 'http')) {
                    return $first;
                }
                // Base64 data URI — serve via streaming route
                if (str_starts_with($first, 'data:')) {
                    return route('marketplace.image', ['id' => $this->id, 'index' => 0]);
                }
                return img($first);
            }
            return null;
        }

        // Already a full public URL (S3, CDN, WhatsApp job upload) — return directly
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }

        // Base64 data URI — serve via dedicated streaming route
        if (str_starts_with($this->cover_image, 'data:')) {
            return route('marketplace.image', ['id' => $this->id, 'index' => 0]);
        }

        // Relative file path (resolves via S3/R2 or local fallback)
        return img($this->cover_image);
    }

    public function getAvailableActionsAttribute()
    {
        $actions = [];
        $cat = strtoupper((string) $this->category);

        if (strpos($cat, 'REAL_ESTATE') === 0 || strpos($cat, 'IMMOBILIER') !== false) {
            if (strpos($cat, 'LOCATION') !== false) {
                // Location : Choix entre réservation (10% séquestre) et location directe (Panier)
                $actions[] = ['type' => 'RESERVE', 'label' => 'Réserver', 'requires_calendar' => true];
                $actions[] = ['type' => 'RENT', 'label' => 'Louer'];
            } else {
                // Achat
                $actions[] = ['type' => 'BUY', 'label' => 'Acheter'];
            }
            $actions[] = ['type' => 'CONTACT', 'label' => 'Contacter'];
        } 
        elseif (strpos($cat, 'VEHICLES') === 0 || strpos($cat, 'VéHICULE') !== false) {
            if (strpos($cat, 'LOCATION') !== false) {
                // Location : Choix entre réservation et location directe
                $actions[] = ['type' => 'RESERVE', 'label' => 'Réserver', 'requires_calendar' => true];
                $actions[] = ['type' => 'RENT', 'label' => 'Louer'];
            } else {
                // Achat
                $actions[] = ['type' => 'BUY', 'label' => 'Acheter'];
            }
            $actions[] = ['type' => 'CONTACT', 'label' => 'Contacter'];
        }
        elseif (strpos($cat, 'TICKETS') === 0 || strpos($cat, 'EVENT') !== false || strpos($cat, 'BILLET') !== false) {
            $actions[] = ['type' => 'BUY_TICKET', 'label' => 'Acheter'];
        }
        elseif (strpos($cat, 'SERVICES') === 0 || strpos($cat, 'CONVOY') === 0) {
            $actions[] = ['type' => 'GET_QUOTE', 'label' => 'Devis'];
            $actions[] = ['type' => 'BOOK', 'label' => 'Réserver'];
            $actions[] = ['type' => 'CONTACT', 'label' => 'Contacter'];
        }
        else {
            // SALE, ELECTRONICS, FASHION, FOOD, etc
            $actions[] = ['type' => 'ADD_TO_CART', 'label' => 'Au Panier'];
            $actions[] = ['type' => 'BUY_NOW', 'label' => 'Acheter'];
        }
        
        return $actions;
    }

    // --- Accessors for Virtual Polymorphic Attributes ---
    public function getBrandAttribute($value) { return $value ?? (($this->metadata ?? [])['brand'] ?? optional($this->listable)->brand); }
    public function getModelAttribute($value) { return $value ?? (($this->metadata ?? [])['model'] ?? optional($this->listable)->model); }
    public function getYearAttribute($value) { return $value ?? (($this->metadata ?? [])['year'] ?? optional($this->listable)->year); }
    public function getColorAttribute($value) { return $value ?? (($this->metadata ?? [])['color'] ?? optional($this->listable)->color); }
    public function getPlateNumberAttribute($value) { return $value ?? (($this->metadata ?? [])['plate_number'] ?? optional($this->listable)->plate_number); }
    public function getWithDriverAttribute($value) { return $value ?? (($this->metadata ?? [])['with_driver'] ?? optional($this->listable)->with_driver); }
    public function getDriverPriceAttribute($value) { return $value ?? (($this->metadata ?? [])['driver_price'] ?? optional($this->listable)->driver_price); }
    public function getDrivingPolicyAttribute($value) { return $value ?? (($this->metadata ?? [])['driving_policy'] ?? optional($this->listable)->driving_policy); }

    public function getLocationCityAttribute($value) { return $value ?? (($this->metadata ?? [])['location_city'] ?? optional($this->listable)->location_city); }
    public function getLocationLatitudeAttribute($value) { return $value ?? (($this->metadata ?? [])['location_latitude'] ?? optional($this->listable)->location_latitude); }
    public function getLocationLongitudeAttribute($value) { return $value ?? (($this->metadata ?? [])['location_longitude'] ?? optional($this->listable)->location_longitude); }
    public function getPriceUnitAttribute($value) { return $value ?? (($this->metadata ?? [])['price_unit'] ?? optional($this->listable)->price_unit); }

    public function getStockQuantityAttribute($value) { return $value ?? (($this->metadata ?? [])['stock_quantity'] ?? optional($this->listable)->stock_quantity); }
    public function getHomeDeliveryAttribute($value) { return $value ?? (($this->metadata ?? [])['home_delivery'] ?? optional($this->listable)->home_delivery); }
    public function getDeliveryPriceAttribute($value) { return $value ?? (($this->metadata ?? [])['delivery_price'] ?? optional($this->listable)->delivery_price); }
    public function getIsDigitalAttribute($value) { return $value ?? (($this->metadata ?? [])['is_digital'] ?? optional($this->listable)->is_digital); }
    public function getDigitalFilePathAttribute($value) { return $value ?? (($this->metadata ?? [])['digital_file_path'] ?? optional($this->listable)->digital_file_path); }

    public function getPdpRouteIdAttribute($value) { return $value ?? (($this->metadata ?? [])['pdp_route_id'] ?? optional($this->listable)->pdp_route_id); }

    public function getPickupAddressAttribute($value) { return $value ?? (($this->metadata ?? [])['pickup_address'] ?? optional($this->listable)->pickup_address); }

    /**
     * Get the associated WhatsApp message if imported via WhatsApp.
     */
    public function whatsappMessage()
    {
        return $this->belongsTo(WhatsappMessage::class, 'whatsapp_message_id');
    }

    /**
     * Check if the listing is purchased by the user
     */
    public function isPurchasedBy($userId)
    {
        if (!$userId) return false;
        return $this->bookings()
            ->where('user_id', $userId)
            ->where('status', 'COMPLETED')
            ->exists();
    }
}
