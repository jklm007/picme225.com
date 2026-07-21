<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement déclenché quand le seuil de Pledges est atteint sur une Intention.
 * Notifie tous les pledgeurs ET les chauffeurs de la zone pour organiser la course.
 */
class PledgeThresholdReached implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $postId,
        public readonly int $pledgeCount,
        public readonly ?int $routeId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('intention.' . $this->postId);
    }

    public function broadcastAs(): string
    {
        return 'pledge_threshold_reached';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'      => $this->postId,
            'pledge_count' => $this->pledgeCount,
            'route_id'     => $this->routeId,
            'message'      => '🎉 Votre groupe est complet ! Un chauffeur va être trouvé pour votre trajet.',
        ];
    }
}
