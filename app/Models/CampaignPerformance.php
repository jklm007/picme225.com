<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignPerformance extends Model
{
    protected $fillable = [
        'ad_campaign_id',
        'ad_platform_id',
        'date',
        'impressions',
        'clicks',
        'conversions',
        'spent',
        'ctr',
        'cpc',
        'cpm',
        'conversion_rate',
        'additional_metrics',
    ];

    protected $casts = [
        'date' => 'date',
        'spent' => 'decimal:2',
        'ctr' => 'decimal:2',
        'cpc' => 'decimal:2',
        'cpm' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'additional_metrics' => 'array',
    ];

    /**
     * Relation avec la campagne
     */
    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    /**
     * Relation avec la plateforme
     */
    public function platform()
    {
        return $this->belongsTo(AdPlatform::class, 'ad_platform_id');
    }
}

