<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionalRoute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'departure_city',
        'destination_city',
        'distance_km',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'distance_km' => 'double',
        'is_active' => 'boolean',
    ];
}
