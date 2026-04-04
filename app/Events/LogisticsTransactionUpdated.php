<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class LogisticsTransactionUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $transaction;

    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    public function broadcastOn()
    {
        return new Channel('logistics');
    }

    public function broadcastWith()
    {
        return ['transaction' => $this->transaction];
    }
}
