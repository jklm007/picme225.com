<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StationAgent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'pdp_stop_id',
        'interurban_company_id',
        'agent_code',
        'is_active',
        'name',
        'email',
        'password',
        'mobile',
        'wallet_balance',
        'commission_per_passenger',
        'commission_per_parcel'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that is the agent.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the station (PDP Stop) managed by this agent.
     */
    public function station()
    {
        return $this->belongsTo(PdpStop::class, 'pdp_stop_id');
    }

    /**
     * Get the company this agent works for.
     */
    public function company()
    {
        return $this->belongsTo(InterurbanCompany::class, 'interurban_company_id');
    }
}
