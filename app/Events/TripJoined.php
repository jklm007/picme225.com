<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement diffusé quand un passager rejoint un trajet partagé.
 * Met à jour le compteur de places restantes en temps réel.
 */
class TripJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $postId,
        public readonly int $seatsRemaining,
        public readonly int $joiningUserId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('trip.' . $this->postId);
    }

    public function broadcastAs(): string
    {
        return 'trip_joined';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'        => $this->postId,
            'seats_remaining'=> $this->seatsRemaining,
        ];
    }
}
