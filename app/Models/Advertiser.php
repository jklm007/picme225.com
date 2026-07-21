<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertiser extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'status',
    ];

    /**
     * Relation avec les campagnes publicitaires
     */
    public function campaigns()
    {
        return $this->hasMany(AdCampaign::class, 'advertiser_id');
    }
}
