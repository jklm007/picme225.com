<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderBonus extends Model
{
    protected $fillable = [
        'provider_id',
        'bonus_type',
        'amount',
        'trigger',
        'description',
        'related_id',
        'related_type',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'paid_at' => 'datetime',
    ];

    /**
     * Relation avec le provider
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Relation polymorphique avec l'entité liée (course, etc.)
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Scope pour filtrer par type de bonus
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('bonus_type', $type);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Obtenir le montant en CFA
     */
    public function getAmountCfaAttribute()
    {
        return $this->amount * 1000; // 1 ECO = 1,000 CFA
    }
}
