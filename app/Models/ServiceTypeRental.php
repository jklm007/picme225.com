<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTypeRental extends Model
{
    	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_type_id','km_hour_id','ren_price'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    public function package()
    {
        return $this->belongsTo('App\Models\KmHour','km_hour_id');
    }
    
}
