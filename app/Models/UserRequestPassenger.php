<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRequestPassenger extends Model
{
    use HasFactory;

    /**
     * Attributs autorisés.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'user_id',
        'passengers_count',
        'segment_type',
        'baggage_count',
        'pickup_pdp_id',
        'dropoff_pdp_id',
        'status',
        'price',
    ];

    /**
     * Requête principale associée.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(UserRequests::class, 'request_id');
    }

    /**
     * Passager rattaché.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Point PDP de prise en charge.
     */
    public function pickupPdp(): BelongsTo
    {
        return $this->belongsTo(PdpStop::class, 'pickup_pdp_id');
    }

    /**
     * Point PDP de dépose.
     */
    public function dropoffPdp(): BelongsTo
    {
        return $this->belongsTo(PdpStop::class, 'dropoff_pdp_id');
    }
}
