<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement diffusé en temps réel quand un nouveau post social est créé (trajet partagé).
 * Permet à l'app mobile d'afficher instantanément le nouveau trajet dans le fil.
 */
class NewSocialTripPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $postId,
        public readonly string $tripType,   // 'TRIP', 'INTENTION', etc.
        public readonly ?int $routeId,       // Corridor de route
        public readonly array $preview       // Aperçu du post pour l'UI
    ) {}

    /** Diffuser sur le canal public du corridor, ou sur le canal global */
    public function broadcastOn(): Channel
    {
        return new Channel('social-hub');
    }

    public function broadcastAs(): string
    {
        return 'NewSocialPost';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'   => $this->postId,
            'trip_type' => $this->tripType,
            'route_id'  => $this->routeId,
            'preview'   => $this->preview,
        ];
    }
}
