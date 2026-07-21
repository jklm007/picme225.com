<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',        // TRIP, NEWS, VIRAL, POLL, SOCIAL, INTENTION, RENTAL, SALE
        'source',      // INTERNAL, ABIDJAN_NET, etc.
        'category',    // TRAFFIC, ACCIDENT, COMMUNITY, BUZZ, TRANSPORT, MARKETPLACE
        'trip_id',
        'trip_type',   // 'request' pour UserRequests, 'shared' pour ActiveSharedRide
        'content',
        'media_url',
        'external_link',
        'likes_count',
        'comments_count',
        'pdp_route_id', // Lien avec le Corridor de Route
        'service_type_id', // Filtrage par type de service (VTC, Taxi, etc.)
        'is_shareable',    // La course accepte-t-elle des passagers supplémentaires ?
        'seats_available', // Nombre de places disponibles pour rejoindre
        'price',           // Prix par siège ou prix global
        'pledge_count',    // Nombre d'engagements (pour les Intentions)
        'pledge_threshold',// Seuil d'engagements pour déclencher une course communautaire
        'poll_id',         // ID du sondage (si type = POLL)
        'status',          // ACTIVE, CLOSED, CANCELLED, PLEDGING
        'expires_at',
        'latitude',
        'longitude',
        'dislikes_count',
        'shares_count',
        'author_type', // USER or PROVIDER
        'published_at',
        'publication_date',
        'publication_time',
    ];

    protected $casts = [
        'likes_count'      => 'integer',
        'comments_count'   => 'integer',
        'seats_available'  => 'integer',
        'pledge_count'     => 'integer',
        'pledge_threshold' => 'integer',
        'poll_id'          => 'integer',
        'dislikes_count'   => 'integer',
        'shares_count'     => 'integer',
        'is_shareable'     => 'boolean',
        'expires_at'       => 'datetime',
        'published_at'     => 'datetime',
        'created_at'       => 'datetime',
    ];

    /** Utilisateur auteur du post */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Prestataire auteur du post */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'user_id');
    }

    /** Accessor pour l'auteur unifié */
    public function getAuthorAttribute()
    {
        return $this->author_type === 'PROVIDER' ? $this->provider : $this->user;
    }

    /** Sondage associé (si type = POLL) */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /** Corridor de route associé (ex: Abidjan-Bassam) */
    public function corridor(): BelongsTo
    {
        return $this->belongsTo(PdpRoute::class, 'pdp_route_id');
    }

    /** Commentaires sur le post */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    /** Likes sur le post */
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /** Pledges (engagements communautaires) pour les intentions de trajet */
    public function pledges(): HasMany
    {
        return $this->hasMany(PostPledge::class);
    }

    /** Le trajet lié (si type = TRIP) */
    public function request(): BelongsTo
    {
        return $this->belongsTo(UserRequests::class, 'trip_id');
    }

    /** Vérifier si le post est une intention avec un seuil atteint */
    public function isPledgeThresholdReached(): bool
    {
        return $this->type === 'INTENTION' && $this->pledge_count >= $this->pledge_threshold;
    }
}
