<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPassType extends Model
{
    use SoftDeletes;

    protected $table = 'event_pass_types';

    protected $fillable = [
        'listing_id',
        'post_id',
        'event_id',
        'name',
        'price',
        'valid_from',
        'valid_until',
        'quantity',
        'sold_count',
        'persons_per_pass'
    ];

    /**
     * Le post social associé à ce pass.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Post::class, 'post_id');
    }

    /**
     * L'annonce marketplace associée.
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MarketplaceListing::class, 'listing_id');
    }

    /**
     * Vérifier si l'heure actuelle est dans la plage de validité du pass.
     */
    public function isCurrentlyValid(): bool
    {
        $now = now()->format('H:i:s');
        
        // Cas classique : 12:00 -> 22:00
        if ($this->valid_from <= $this->valid_until) {
            return $now >= $this->valid_from && $now <= $this->valid_until;
        }
        
        // Cas "Jusqu'à l'aube" : 20:00 -> 06:00 (le lendemain)
        return $now >= $this->valid_from || $now <= $this->valid_until;
    }
}
