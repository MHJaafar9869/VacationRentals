<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("chat.{guestId}.{hostId}.{bookingId}", function ($user, $guestId, $hostId) {
    return true;
});
