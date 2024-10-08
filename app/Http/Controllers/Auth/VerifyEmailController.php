<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            return response()->json([
                'message' => 'Email successfully verified. Please log in.',
                'verified_at' => $request->user()->email_verified_at,
                'redirect_url' => route('login')
            ], 200);
        }

        return response()->json(['message' => 'Unable to verify email.'], 500);
    }
}