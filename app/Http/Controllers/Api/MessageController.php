<?php

namespace App\Http\Controllers\Api;

use App\Events\Message;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Owner;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => 'required',
            'bookingId' => 'required',
            'username' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'All fields are required',
                'errors' => $validator->errors()
            ], 400);
        }

        $guestId = $request->input("guest_id");
        $username = $request->input("username");
        $message = $request->input("message");
        $bookingId = $request->input("bookingId");

        $booking = Booking::with('property.owner')->find($bookingId);

        if (!$booking) {
            return response()->json([
                'code' => 404,
                'message' => 'No booking found with that ID'
            ], 404);
        }

        $hostId = $booking->property->owner->id;

        if ($booking->guest_id !== $guestId) {
            return response()->json([
                'code' => 403,
                'message' => 'Guest ID does not match the booking'
            ], 403);
        }

        event(new Message($username, $message, $guestId, $hostId, $bookingId));

        Log::info("Event triggered: {$username}, {$message}, Guest ID: {$guestId}, Host ID: {$hostId}, Booking ID: {$bookingId}");

        return response()->json([
            'message' => 'Message sent successfully.',
            'content' => new Message($username, $message, $guestId, $hostId, $bookingId)
        ], 200);
    }


    public function getRoomDetails($propertyId, $userId, $bookingId)
    {
        $room = Room::where('property_id', $propertyId)
            ->where('booking_id', $bookingId)
            ->where(function ($query) use ($userId) {
                $query->where('guest_id', $userId)
                    ->orWhere('host_id', $userId);
            })
            ->first();

        if ($room) {
            return response()->json([
                'status' => 200,
                'message' => 'Data retrieved successfully',
                'data' => $room,
            ]);
        }

        return response()->json(['status' => 404, 'message' => 'Room not found.', 'data' => []], 200);
    }



}