<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement SOS déclenché depuis une course sociale.
 * L'alerte est épinglée sur le fil du corridor pour prévenir la communauté.
 */
class SocialSosAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $postId,
        public readonly int    $userId,
        public readonly float  $latitude,
        public readonly float  $longitude,
        public readonly ?int   $routeId,
        public readonly string $timestamp
    ) {}

    /** Diffuser sur le canal du corridor ET sur le canal global urgent */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('alerts.sos'),
            new Channel('social-hub') // Added for social feed real-time display
        ];
        if ($this->routeId) {
            $channels[] = new Channel('corridor.' . $this->routeId);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'SocialSosAlert';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id'   => $this->postId,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'route_id'  => $this->routeId,
            'timestamp' => $this->timestamp,
            'message'   => '🚨 Alerte SOS sur votre corridor ! Un membre a besoin d\'aide.',
        ];
    }
}
