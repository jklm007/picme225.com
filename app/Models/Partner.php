<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'partner_code',
        'type',
        'status',
        'tier',
        'company_name',
        'logo',
        'pdp_stop_id',
        'interurban_company_id',
        'commission_rules',
        'metadata',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'commission_rules' => 'array',
        'metadata'         => 'array',
    ];

    /**
     * Get the user linked to this partner.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the station (PDP Stop) managed by this partner (if Station Agent).
     */
    public function pdpStop()
    {
        return $this->belongsTo(PdpStop::class, 'pdp_stop_id');
    }

    /**
     * Alias for pdpStop() — used by controllers and legacy code that reference ->station.
     */
    public function station()
    {
        return $this->belongsTo(PdpStop::class, 'pdp_stop_id');
    }

    /**
     * Get the company this partner works for or owns.
     */
    public function company()
    {
        return $this->belongsTo(InterurbanCompany::class, 'interurban_company_id');
    }

    /**
     * Get affiliates (users/providers onboarded by this partner).
     */
    public function affiliates()
    {
        return $this->hasMany(PartnerAffiliate::class);
    }

    /**
     * Get providers (drivers) directly managed by this partner (e.g. if Fleet Owner).
     */
    public function providers()
    {
        return $this->hasMany(Provider::class, 'partner_id');
    }

    /**
     * Helper to get customized commission rate or fallback to default.
     *
     * @param string $key e.g. 'passenger_scan_cfa', 'parcel_cfa', 'trip_share_percent'
     * @param mixed $default
     * @return mixed
     */
    public function getCommissionRule($key, $default = null)
    {
        if ($this->commission_rules && isset($this->commission_rules[$key])) {
            return $this->commission_rules[$key];
        }
        return $default;
    }
}
