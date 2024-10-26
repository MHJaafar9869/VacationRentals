<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{

    // User adds a review and rating
    public function addReview(Request $request)
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'review' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'property_id' => $request->property_id,
            'review' => $request->review,
            'rating' => $request->rating
        ]);
        $review->load('user');
        return response()->json(['message' => 'Review submitted for moderation!', 'review' => $review,
      'user' => $review->user->name,
    ], 201);
    }


    public function checkBooking($propertyId)
    {
        // Check if the authenticated user has booked the property
        $hasBooked = Booking::where('user_id', Auth::id())
            ->where('property_id', $propertyId)
            ->exists();

        if ($hasBooked) {
            // dd('ss');
            return response()->json(['canReview' => true, 'message' => 'User has booked this property'], 200);
        } else {
            // dd('aa');
            return response()->json(['canReview' => false, 'message' => 'User has not booked this property'], 403);
        }
    }


    // Get reviews for a property
    public function getPropertyReviews($propertyId)
    {
        $reviews = Review::where('property_id', $propertyId)->with('user')->get();

        return response()->json($reviews);
    }
    public function deleteReview($reviewId)
    {


        $review = Review::find($reviewId);
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully.'], 200);
    }
}