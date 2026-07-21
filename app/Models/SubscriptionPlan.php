<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'target',
        'service_id',
        'description',
        'badge_url',
        'price',
        'period',
        'commission_type',
        'commission_value',
        'fixed_fee',
        'priority',
        'priority_weight',
        'insurance_included',
        'staking_bonus_percentage',
        'status',
        'show_on_marketplace',
        'max_categories',
    ];

    protected $casts = [
        'price'                    => 'float',
        'commission_value'         => 'float',
        'fixed_fee'                => 'float',
        'staking_bonus_percentage' => 'float',
        'insurance_included'       => 'boolean',
        'show_on_marketplace'      => 'boolean',
        'status'                   => 'boolean',
        'priority'                 => 'integer',
        'priority_weight'          => 'integer',
        'max_categories'           => 'integer',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Plans for drivers / providers */
    public function scopeForProviders(Builder $query): Builder
    {
        return $query->where('target', 'provider');
    }

    /** Plans for marketplace sellers/merchants */
    public function scopeForMarketplace(Builder $query): Builder
    {
        return $query->where('target', 'marketplace');
    }

    /** Only active plans */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    // ─── Relations ────────────────────────────────────────────────────────────

    public function serviceTypes()
    {
        return $this->hasMany(SubscriptionPlanServiceType::class);
    }

    public function serviceCommissions()
    {
        return $this->hasMany(SubscriptionPlanServiceCommission::class);
    }

    /** Providers (drivers) currently on this plan */
    public function activeProviders()
    {
        return $this->hasMany(Provider::class, 'subscription_plan_id')
                    ->where('subscription_expires_at', '>', now());
    }

    /** Users (passengers) currently on this marketplace plan */
    public function activeMarketplaceUsers()
    {
        return $this->hasMany(User::class, 'marketplace_plan_id')
                    ->where('marketplace_plan_expires_at', '>', now());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isMarketplace(): bool
    {
        return $this->target === 'marketplace';
    }

    public function isProviderPlan(): bool
    {
        return $this->target === 'provider';
    }

    /**
     * Human-readable period label.
     */
    public function periodLabel(): string
    {
        return match($this->period) {
            'DAILY'  => 'Jour',
            'WEEKLY' => 'Semaine',
            'YEARLY' => 'An',
            default  => 'Mois',
        };
    }
}
