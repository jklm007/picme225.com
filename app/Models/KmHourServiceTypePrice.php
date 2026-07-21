<?php


namespace App\Models; // ou App

use Illuminate\Database\Eloquent\Model;

class KmHourServiceTypePrice extends Model
{
    protected $fillable = [
        'km_hour_id',
        'service_type_id',
        'price',
    ];

    protected $casts = [
        'price' => 'float',
    ];

    public function kmHour()
    {
        return $this->belongsTo(KmHour::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
