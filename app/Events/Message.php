<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class Message implements ShouldBroadcastNow
{
    public function __construct(
        public string $username,
        public string $message,
        public int $guestId,
        public int $hostId,
        public int $bookingId
    ) {}

    public function broadcastOn()
    {
        return new Channel("chat.{$this->guestId}.{$this->hostId}.{$this->bookingId}");
    }

    public function broadcastAs()
    {
        return 'message';
    }
}
