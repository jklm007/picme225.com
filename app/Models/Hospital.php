<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hospital_address','latitude','longitude', 'is_available', 'contact_phone', 'zone_coverage_radius_km'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function providerservice()
    {
        return $this->hasmany('App\Models\ProviderService');
    }
}
