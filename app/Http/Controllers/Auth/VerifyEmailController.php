<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        // Check if the email is already verified
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        // Mark the email as verified
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json(['message' => 'Email successfully verified.'], 200);
    }
}
