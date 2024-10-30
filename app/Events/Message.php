<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class Message implements ShouldBroadcastNow
{
    public string $username;
    public string $message;
    public int $guestId;
    public int $hostId;
    public int $bookingId;
    public function __construct(
        string $username,
        string $message,
        int $guestId,
        int $hostId,
        int $bookingId
    ) {
        $this->username = $username;
        $this->message = $message;
        $this->guestId = $guestId;
        $this->hostId = $hostId;
        $this->bookingId = $bookingId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("chat.{$this->guestId}.{$this->hostId}.{$this->bookingId}");
    }

    public function broadcastAs()
    {
        return 'message';
    }
}