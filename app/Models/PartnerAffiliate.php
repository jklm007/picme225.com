<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerAffiliate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'partner_id',
        'affiliated_user_id',
        'affiliated_provider_id',
        'affiliated_type',
        'status',
        'commission_earned',
    ];

    /**
     * Get the partner.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the affiliated user.
     */
    public function affiliatedUser()
    {
        return $this->belongsTo(User::class, 'affiliated_user_id');
    }
}
