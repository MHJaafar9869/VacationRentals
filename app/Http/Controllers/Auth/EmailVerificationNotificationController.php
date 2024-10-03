<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if the user's email is already verified
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        // Send the email verification notification
        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification link sent'], 200);
    }
}
