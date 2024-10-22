<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel("private-chat.{guestId}.{hostId}.{bookingId}", function ($user, $guestId, $hostId, $bookingId) {
    Log::info("Checking access for user: {$user->id}, guest: {$guestId}, host: {$hostId}, booking: {$bookingId}");

    if ((int) $user->id == (int) $guestId || (int) $user->id == (int) $hostId) {
        return true;
    }
    Log::info("Unauthorized access attempt by user {$user->id} for private-chat.{$guestId}.{$hostId}.{$bookingId}");
    return false;
});