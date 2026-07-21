<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderSelectedService extends Model
{
    protected $table = 'provider_selected_services';

    protected $fillable = [
        'provider_id',
        'service_id',
        'is_active',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
