<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel("chat.{guestId}.{hostId}.{bookingId}", function ($user, $guestId, $hostId) {
    if ((int) $user->id === (int) $guestId || (int) $user->id === (int) $hostId) {
        return true;
    }
    return false;
});