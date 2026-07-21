<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle pour les Pledges (engagements communautaires) sur une Intention de Trajet.
 * Quand pledge_count >= pledge_threshold sur le Post parent, une course communautaire est déclenchée.
 */
class PostPledge extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'status', // PLEDGED, CONFIRMED (après déclenchement), CANCELLED
    ];

    protected $casts = [
        'pickup_latitude'  => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
