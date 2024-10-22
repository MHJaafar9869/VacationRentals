<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Message implements ShouldBroadcast
{
    public function __construct(
        public string $username,
        public string $message,
        public int $guestId,
        public int $hostId,
        public int $bookingId,
    ) {
        //
    }

    public function broadcastOn()
    {
        return new PrivateChannel("private-chat.{$this->guestId}.{$this->hostId}.{$this->bookingId}");
    }

    public function broadcastAs()
    {
        return 'message';
    }
}
