<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ProcurementRequestUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $procurementRequest;

    public function __construct($procurementRequest)
    {
        $this->procurementRequest = $procurementRequest;
    }

    public function broadcastOn()
    {
        return new Channel('procurement');
    }

    public function broadcastWith()
    {
        return ['procurement_request' => $this->procurementRequest];
    }
}
