<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialInteractionUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;
    public $likesCount;
    public $dislikesCount;

    /**
     * Create a new event instance.
     */
    public function __construct(int $postId, int $likesCount, int $dislikesCount)
    {
        $this->postId = $postId;
        $this->likesCount = $likesCount;
        $this->dislikesCount = $dislikesCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('social-hub'),
        ];
    }

    public function broadcastAs()
    {
        return 'SocialInteractionUpdate';
    }
}
