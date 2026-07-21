<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxExemptionConfig extends Model
{
    protected $table = 'tax_exemption_config';
    
    protected $fillable = [
        'is_active',
        'start_date',
        'end_date',
        'duration_months',
        'allocation_percentages'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'allocation_percentages' => 'array'
    ];
}
