<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'email',
        'mobile',
        'picture',
        'password',
        'device_type',
        'device_token',
        'login_by',
        'payment_mode',
        'social_unique_id',
        'device_id',
        'wallet_address',
        'language',
        'subscription_plan_id',
        'subscription_expires_at',
        'kyc_status',
        'kyc_document_type',
        'kyc_document_front',
        'kyc_document_back',
        'kyc_license_number',
        'kyc_rejected_reason',
        'kyc_verified_at',
        'social_points',
        'social_rating',
        'wallet_balance',
        'user_badge',
        'is_verified',
        'referral_unique_id',
        'referral_count',
        'referred_by_id',
        'referred_by_type',
        'trust_score',
        'display_name',
        'discount_trips_remaining',
        'current_discount_rate',
        'qr_id',
        'qr_token',
        'cart_data',
        'marketplace_favorites',
        // Marketplace subscription (Starter / Pro / Business for sellers)
        'marketplace_plan_id',
        'marketplace_plan_expires_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->qr_token = \Illuminate\Support\Str::random(40);
        });

        static::created(function ($model) {
            $model->qr_id = 'PM-U' . str_pad($model->id, 6, '0', STR_PAD_LEFT);
            $model->save();
        });
    }

    /**
     * Synchronise le badge et les quotas de réduction selon les points Karma.
     */
    public function syncKarmaBadge()
    {
        $points = $this->social_points;
        $oldBadge = $this->user_badge;

        if ($points >= 10000) {
            $this->user_badge = 'Légende';
            $this->current_discount_rate = 0.10;
            $this->discount_trips_remaining = -1; // Illimité
        } elseif ($points >= 5000) {
            $this->user_badge = 'Ambassadeur';
            $this->current_discount_rate = 0.05;
            $this->discount_trips_remaining = -1; // Illimité
        } elseif ($points >= 2500) {
            if ($oldBadge !== 'Membre Influent' && $oldBadge !== 'Ambassadeur' && $oldBadge !== 'Légende') {
                $this->current_discount_rate = 0.10;
                $this->discount_trips_remaining = 25;
            }
            $this->user_badge = 'Membre Influent';
        } elseif ($points >= 1000) {
            if ($oldBadge !== 'Collaborateur' && $oldBadge !== 'Membre Influent' && $oldBadge !== 'Ambassadeur' && $oldBadge !== 'Légende') {
                $this->current_discount_rate = 0.10;
                $this->discount_trips_remaining = 10;
            }
            $this->user_badge = 'Collaborateur';
        } elseif ($points >= 500) {
            if ($oldBadge !== 'Membre Actif' && $oldBadge !== 'Collaborateur' && $oldBadge !== 'Membre Influent' && $oldBadge !== 'Ambassadeur' && $oldBadge !== 'Légende') {
                $this->current_discount_rate = 0.10;
                $this->discount_trips_remaining = 5;
            }
            $this->user_badge = 'Membre Actif';
        } else {
            $this->user_badge = 'Explorateur';
            $this->current_discount_rate = 0;
            $this->discount_trips_remaining = 0;
        }

        $this->save();
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at'
    ];

    public function findForPassport($username)
    {
        \Log::info("Passport login attempt for: " . $username);
        // Nettoyage du numéro de mobile pour la comparaison
        // On enlève le '+', et les prefixes '225' ou '00225'
        $cleanedMobile = preg_replace('/^\+/', '', $username);
        $cleanedMobile = preg_replace('/^00225/', '', $cleanedMobile);
        $cleanedMobile = preg_replace('/^225/', '', $cleanedMobile);

        $user = $this->where('email', $username)
            ->orWhere('mobile', $username)
            ->orWhere('mobile', $cleanedMobile)
            ->orWhere('mobile', '+' . $cleanedMobile)
            ->orWhere('mobile', '225' . $cleanedMobile)
            ->first();

        if ($user) {
            \Log::info("User found: " . $user->email);
        } else {
            \Log::info("User NOT found for: " . $username);
        }

        return $user;
    }

    /**
     * The services that belong to the user.
     */
    public function trips()
    {
        return $this->hasMany('App\Models\UserRequests', 'user_id', 'id');
    }

    /**
     * If user_type is FLEET, return the linked fleet profile.
     */
    public function fleet()
    {
        return $this->belongsTo('App\Models\Fleet', 'fleet_id');
    }

    /**
     * If user_type is STATION_AGENT, return the agent profile.
     */
    public function stationAgent()
    {
        return $this->hasOne('App\Models\StationAgent', 'user_id');
    }

    /**
     * Get the partner profile associated with this user.
     */
    public function partner()
    {
        return $this->hasOne('App\Models\Partner', 'user_id');
    }

    /**
     * Get the legacy transport subscription plan linked to the user.
     * @deprecated Use marketplacePlan() or UserSubscriptionSchedule for transport.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo('App\Models\SubscriptionPlan', 'subscription_plan_id');
    }

    /**
     * Get the Marketplace subscription plan (Starter / Pro / Business).
     * This is for sellers / merchants who pay a fixed monthly fee.
     */
    public function marketplacePlan()
    {
        return $this->belongsTo('App\Models\SubscriptionPlan', 'marketplace_plan_id');
    }

    /**
     * Check if the user has an active Marketplace subscription.
     */
    public function hasActiveMarketplaceSubscription(): bool
    {
        return $this->marketplace_plan_id
            && $this->marketplace_plan_expires_at
            && \Carbon\Carbon::now()->lt($this->marketplace_plan_expires_at);
    }

    /**
     * Check if the user has an active transport commute schedule.
     * A schedule is active if it exists with status=ACTIVE and expires_at is in the future.
     */
    public function hasActiveTransportSchedule(): bool
    {
        return $this->transportSchedules()
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', \Carbon\Carbon::now())
            ->exists();
    }

    /**
     * Get all active recurring commute schedules for this user.
     */
    public function transportSchedules()
    {
        return $this->hasMany('App\Models\UserSubscriptionSchedule', 'user_id');
    }

    /**
     * @deprecated Use hasActiveMarketplaceSubscription() for marketplace plans
     *             or hasActiveTransportSchedule() for commute scheduling.
     * Kept for backward compatibility with CheckSubscription middleware.
     */
    public function hasActiveSubscription(): bool
    {
        // Check marketplace subscription first
        if ($this->hasActiveMarketplaceSubscription()) {
            return true;
        }
        // Fallback: legacy provider subscription_plan_id field
        return (bool) ($this->subscription_plan_id
            && $this->subscription_expires_at
            && \Carbon\Carbon::now()->lt($this->subscription_expires_at));
    }
}
