<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdContent extends Model
{
    protected $fillable = [
        'ad_campaign_id',
        'content_type',
        'title',
        'headline',
        'description',
        'call_to_action',
        'image_url',
        'video_url',
        'audio_url',
        'keywords',
        'platform_specific_data',
        'is_ai_generated',
        'ai_prompt',
    ];

    protected $casts = [
        'keywords' => 'array',
        'platform_specific_data' => 'array',
        'is_ai_generated' => 'boolean',
    ];

    /**
     * Relation avec la campagne
     */
    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }
}

