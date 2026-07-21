<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdPlatform extends Model
{
    protected $fillable = [
        'ad_campaign_id',
        'platform',
        'platform_campaign_id',
        'status',
        'spent',
        'platform_config',
        'error_message',
    ];

    protected $casts = [
        'spent' => 'decimal:2',
        'platform_config' => 'array',
    ];

    /**
     * Relation avec la campagne
     */
    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    /**
     * Relation avec les performances
     */
    public function performances()
    {
        return $this->hasMany(CampaignPerformance::class, 'ad_platform_id');
    }
}

