<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdImpression extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ad_campaign_id',
        'user_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relation avec la campagne
     */
    public function campaign()
    {
        return $this->belongsTo(AdCampaign::class, 'ad_campaign_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
