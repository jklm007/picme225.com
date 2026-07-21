<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KmHour extends Model
{
    	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kilometer','hour'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];


   public function serviceTypePrices()
    {
        return $this->hasMany(KmHourServiceTypePrice::class);
    }

    // Méthode utilitaire pour obtenir le prix pour un service_type_id spécifique
    public function getPriceForServiceType($serviceTypeId)
    {
        $priceEntry = $this->serviceTypePrices()->where('service_type_id', $serviceTypeId)->first();
        return $priceEntry ? $priceEntry->price : null;
    }    

}
