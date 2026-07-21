<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterurbanCompany extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type', // BIG or SMALL
        'logo',
        'contact_phone',
        'contact_email',
        'address',
        'is_active',
        'fleet_id',
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
     * Get the stops associated with the company.
     */
    public function stops()
    {
        return $this->hasMany(PdpStop::class, 'interurban_company_id');
    }

    /**
     * Get the routes associated with the company.
     */
    public function routes()
    {
        return $this->hasMany(PdpRoute::class, 'interurban_company_id');
    }

    /**
     * Get the fleet owner of this company.
     */
    public function fleet()
    {
        return $this->belongsTo(Fleet::class);
    }

    /**
     * Get the service types (vehicle categories) for this company.
     */
    public function serviceTypes()
    {
        return $this->hasMany(ServiceType::class);
    }
}
