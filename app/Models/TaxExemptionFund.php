<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxExemptionFund extends Model
{
    protected $table = 'tax_exemption_fund';
    
    protected $fillable = [
        'payment_id',
        'virtual_tva_amount',
        'allocation_type',
        'allocated_amount',
        'notes',
        'exemption_end_date'
    ];
    
    protected $casts = [
        'virtual_tva_amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'exemption_end_date' => 'date'
    ];
    
    public function payment()
    {
        return $this->belongsTo(\App\Models\UserRequestPayment::class, 'payment_id');
    }
}
