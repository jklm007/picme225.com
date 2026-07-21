<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSlot extends Model
{
    protected $fillable = [
        'name',
        'description',
        'admob_unit_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec les campagnes publicitaires
     */
    public function campaigns()
    {
        return $this->hasMany(AdCampaign::class, 'ad_slot_id');
    }
}
