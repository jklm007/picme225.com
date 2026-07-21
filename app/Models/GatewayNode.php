<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'network',
        'type',
        'status',
        'current_balance',
        'daily_volume',
        'monthly_volume',
        'daily_limit',
        'monthly_limit'
    ];
}
