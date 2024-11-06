<?php

namespace App\Http\Controllers\Api;

use App\Events\Message;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Models\Owner;
use Illuminate\Support\Facades\Log;
use App\Models\Message as ModelMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function message(Request $request)
    {
        Log::info('Request Data:', $request->all());
        $validator = Validator::make($request->all(), rules: [
            'guest_id' => 'required|integer',
            'host_id' => 'required|integer',
            'booking_id' => 'required|integer',
            'sender_role' => 'required|string|in:guest,owner',
            'username' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'All fields are required',
                'errors' => $validator->errors()
            ], 400);
        }

        $guestId = $request->input("guest_id");
        $hostId = $request->input("host_id");
        $bookingId = $request->input("booking_id");
        $senderRole = $request->input("sender_role");
        $username = $request->input("username");
        $message = $request->input("message");

        $booking = Booking::with('property.owner')->find($bookingId);

        if (!$booking) {
            return response()->json([
                'code' => 404,
                'message' => "No booking found with the ID: {$bookingId}",
            ], 404);
        }

        if ($booking->guest_id !== $guestId && $booking->property->owner_id !== $hostId) {
            return response()->json([
                'code' => 403,
                'message' => "Unauthorized Access: User is neither host nor guest."
            ], 403);
        }

        event(new Message($username, $message, $guestId, $hostId, $bookingId));

        $message_data = $this->storeMessage($hostId, $guestId, $bookingId, $senderRole, $message);

        return response()->json([
            'status' => 201,
            'message' => 'Message sent successfully.',
            'data' => $message_data
        ], 201);
    }

    private function storeMessage(int $hostId, int $guestId, int $bookingId, string $senderRole, string $message)
    {
        return ModelMessage::create([
            'owner_id' => $hostId,
            'guest_id' => $guestId,
            'booking_id' => $bookingId,
            'sender' => $senderRole,
            'message' => $message
        ]);
    }


    public function getRoomDetails($userId, $bookingId)
    {
        $room = Room::with('messages')
            ->where('booking_id', '=', $bookingId)
            ->where(function ($query) use ($userId) {
                $query->where('guest_id', '=', $userId)
                    ->orWhere('host_id', '=', $userId);
            })
            ->orderBy('created_at', 'DESC')
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

    public function getMessagesPerBooking(Request $request, $bookingId)
    {
        $messages = ModelMessage::with(['owner', 'guest'])
            ->where('booking_id', $bookingId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => 200,
            'messages' => $messages
        ], 200);
    }
}
