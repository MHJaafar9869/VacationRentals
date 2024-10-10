<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function userData($id)
    {
        $AUTH_ID = auth()->id();

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
}