<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'advertiser_id',
        'name',
        'description',
        'status',
        'campaign_type',
        'budget',
        'daily_budget',
        'start_date',
        'end_date',
        'target_audience',
        'ai_generated_content',
        'is_ai_optimized',
        'max_impressions',
        'max_clicks',
        'current_impressions',
        'current_clicks',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'daily_budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'target_audience' => 'array',
        'ai_generated_content' => 'array',
        'is_ai_optimized' => 'boolean',
        'max_impressions' => 'integer',
        'max_clicks' => 'integer',
        'current_impressions' => 'integer',
        'current_clicks' => 'integer',
    ];

    /**
     * Relation avec l'annonceur
     */
    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class, 'advertiser_id');
    }

    /**
     * Relation avec les emplacements (slots)
     */
    public function adSlots()
    {
        return $this->belongsToMany(AdSlot::class, 'ad_campaign_ad_slot', 'ad_campaign_id', 'ad_slot_id');
    }

    /**
     * Relation avec les impressions
     */
    public function impressions()
    {
        return $this->hasMany(AdImpression::class, 'ad_campaign_id');
    }

    /**
     * Relation avec les clics
     */
    public function clics()
    {
        return $this->hasMany(AdClick::class, 'ad_campaign_id');
    }

    /**
     * Relation avec l'utilisateur (client)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec les contenus publicitaires
     */
    public function contents()
    {
        return $this->hasMany(AdContent::class, 'ad_campaign_id');
    }

    /**
     * Relation avec les plateformes
     */
    public function platforms()
    {
        return $this->hasMany(AdPlatform::class, 'ad_campaign_id');
    }

    /**
     * Relation avec les performances
     */
    public function performances()
    {
        return $this->hasMany(CampaignPerformance::class, 'ad_campaign_id');
    }

    /**
     * Calculer le montant total dépensé
     */
    public function getTotalSpentAttribute()
    {
        return $this->platforms()->sum('spent');
    }

    /**
     * Vérifier si la campagne est active
     */
    public function isActive()
    {
        return $this->status === 'ACTIVE' && 
               now()->between($this->start_date, $this->end_date ?? now()->addYear());
    }
}

