<?php

namespace App\Models;

use App\Notifications\ProviderResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class Provider extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public function findForPassport($username)
    {
        \Log::info("Provider Passport login attempt for: " . $username);
        $cleanedMobile = preg_replace('/^\+/', '', $username);
        $cleanedMobile = preg_replace('/^00225/', '', $cleanedMobile);
        $cleanedMobile = preg_replace('/^225/', '', $cleanedMobile);

        $provider = $this->where('email', $username)
            ->orWhere('mobile', $username)
            ->orWhere('mobile', $cleanedMobile)
            ->orWhere('mobile', '+' . $cleanedMobile)
            ->orWhere('mobile', '225' . $cleanedMobile)
            ->first();

        if ($provider) {
            \Log::info("Provider found: " . $provider->email);
        } else {
            \Log::info("Provider NOT found for: " . $username);
        }

        return $provider;
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'email',
        'password',
        'mobile',
        'driver_license_no',
        'address',
        'picture',
        'gender',
        'latitude',
        'longitude',
        'status',
        'avatar',
        'social_unique_id',
        'fleet',
        'language',
        'commune',
        'available',
        'service_type_id',
        'subscription_plan_id',
        'passengers_count',
        'device_token',
        'device_id',
        'device_type',
        'login_by',
        'eco_bonus_expires_at',
        'bonus_expires_at',
        'is_smart_mode',
        'smart_mode_type',
        'smart_dest_lat',
        'smart_dest_lng',
        'smart_dest_address',
        'smart_zone_radius',
        'smart_communes',
        'smart_quota_count',
        'smart_last_used_at',
        'last_priority_action_at',
        'social_points',
        'social_rating',
        'wallet_balance',
        'eco_wallet_balance',
        'wallet_address',
        'is_verified',
        'user_badge',
        'trust_score',
        'qr_id',
        'qr_token',
        'referral_unique_id',
        'referred_by_id',
        'referred_by_type',
        'referral_count',
        'partner_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->qr_token = \Illuminate\Support\Str::random(40);
            
            // Offrir 3 mois d'essai gratuit pour tous les nouveaux comptes Chauffeur
            $model->subscription_expires_at = \Carbon\Carbon::now()->addMonths(3);
            $model->subscription_level = 'standard';
        });

        static::created(function ($model) {
            $model->qr_id = 'PM-P' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
            $model->save();
        });
    }

    // Constantes pour les niveaux d'abonnement (DAO)
    const SUBSCRIPTION_NONE = 'none';
    const SUBSCRIPTION_STANDARD = 'standard';
    const SUBSCRIPTION_ECO = 'eco';
    const SUBSCRIPTION_PRO = 'pro';
    const SUBSCRIPTION_GOLD = 'gold';

    protected $hidden = [
        'password',
        'remember_token',
        'otp'
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'available' => 'boolean',
        'passengers_count' => 'integer',
        'rating' => 'double',
        'fleet' => 'integer',
        'eco_bonus_expires_at' => 'datetime',
        'is_smart_mode' => 'boolean',
        'smart_dest_lat' => 'double',
        'smart_dest_lng' => 'double',
        'smart_zone_radius' => 'double',
        'smart_quota_count' => 'integer',
        'smart_last_used_at' => 'datetime',
        'priority' => 'integer',
        'daily_cancelled_count' => 'integer',
        'daily_timeout_count' => 'integer',
        'completion_streak' => 'integer',
        'last_priority_action_at' => 'date',
        'wallet_balance' => 'double',
        'eco_wallet_balance' => 'double',
        'is_verified' => 'boolean',
    ];

    // Constantes pour les statuts
    const STATUS_AVAILABLE = 'approved';
    const STATUS_RIDING = 'riding';
    const STATUS_OFFLINE = 'onboarding';

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;
        $this->attributes['available'] = ($value === self::STATUS_AVAILABLE);
    }

    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isRiding()
    {
        return $this->status === self::STATUS_RIDING;
    }

    // Relations
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function service()
    {
        return $this->hasOne('App\Models\ProviderService');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function incoming_requests()
    {
        return $this->hasMany('App\Models\RequestFilter')->where('status', 0);
    }

    public function requests()
    {
        return $this->hasMany('App\Models\RequestFilter');
    }

    public function profile()
    {
        return $this->hasOne('App\Models\ProviderProfile');
    }

    public function device()
    {
        return $this->hasOne('App\Models\ProviderDevice');
    }

    public function trips()
    {
        return $this->hasMany('App\Models\UserRequests');
    }

    public function accepted()
    {
        return $this->hasMany('App\Models\UserRequests', 'provider_id')
            ->where('status', '!=', 'CANCELLED');
    }

    public function cancelled()
    {
        return $this->hasMany('App\Models\UserRequests', 'provider_id')
            ->where('status', 'CANCELLED');
    }

    public function documents()
    {
        return $this->hasMany('App\Models\ProviderDocument');
    }

    public function document($id)
    {
        return $this->hasOne('App\Models\ProviderDocument')->where('document_id', $id)->first();
    }

    public function pending_documents()
    {
        return $this->hasMany('App\Models\ProviderDocument')->where('status', 'ASSESSING')->count();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'driver_user')
            ->withPivot('pickup_latitude', 'pickup_longitude', 'destination_latitude', 'destination_longitude', 'dropped_off')
            ->withTimestamps();
    }

    public function selectedServices()
    {
        return $this->hasMany(ProviderSelectedService::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ProviderResetPassword($token));
    }

    /**
     * Check if provider can afford the commission for a ride.
     * 
     * @param float $ridePrice
     * @param int $commissionPercentage
     * @return bool
     */
    public function canAffordCommission($ridePrice)
    {
        $daoService = new \App\Services\DaoDistributionService();
        $commissionPercentage = $daoService->getProviderCommissionRate($this);
        $commission = ($ridePrice * $commissionPercentage) / 100;
        $commissionEco = $commission / 1000.0;
        return $this->eco_wallet_balance >= $commissionEco;
    }

    /**
     * Get the current dispatch priority based on the subscription plan.
     * 
     * @return int
     */
    public function getDispatchPriority()
    {
        return $this->subscriptionPlan ? $this->subscriptionPlan->priority : 0;
    }

    protected $dates = [
        'bonus_expires_at',
        'last_priority_action_at',
        'subscription_expires_at',
    ];
}
