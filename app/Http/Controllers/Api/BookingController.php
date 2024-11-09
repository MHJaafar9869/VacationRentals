<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Block;
use App\Models\Booking;
use App\Models\User;
use App\Models\Owner;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function userData($id)
    {
        $AUTH_ID = auth('sanctum')->id();

        if (!$AUTH_ID) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($id != $AUTH_ID) {
            return response()->json([
                "status" => 403,
                "error" => "Unauthorized Access",
                "data" => []
            ], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                "code" => 401,
                "error" => "No user found with this id",
                "data" => []
            ], 401);
        }

        return response()->json([
            'code' => 200,
            'message' => 'data retrieved successfully',
            'data' => new UserResource($user),
        ], 200);
    }

    public function getOwnerInfo($id)
    {
        $owner = Owner::find($id)->where('role', 'owner')->first();

        if (!$owner) {
            return response()->json([
                'code' => 404,
                'message' => 'No record found',
                'data' => []
            ]);
        }
        return response()->json([
            'code' => 200,
            'message' => 'data retrieved successfully',
            'data' => $owner,
        ]);
    }

    public function checkIfBooked(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid date format or missing fields',
                'error' => $validator->errors()
            ]);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $isBooked = Booking::where('property_id', $id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })->exists();

        $isBlocked = Block::where('property_id', $id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })->exists();

        if ($isBooked || $isBlocked) {
            return response()->json([
                'status' => 200,
                'available' => false,
                'message' => "Property is not available from {$startDate} - {$endDate}"
            ], 200);
        }

        return response()->json([
            'status' => 200,
            'available' => true,
            'message' => "Property is available from {$startDate} - {$endDate}"
        ]);
    }
}