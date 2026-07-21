<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SharedRideLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideId;
    public $latitude;
    public $longitude;
    public $bearing;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($rideId, $latitude, $longitude, $bearing = 0)
    {
        $this->rideId = $rideId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->bearing = $bearing;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('social-ride.' . $this->rideId);
    }

    /**
     * Custom Broadcast Name
     */
    public function broadcastAs()
    {
        return 'LocationUpdate';
    }
}
