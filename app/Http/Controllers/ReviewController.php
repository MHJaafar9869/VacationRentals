<?php

namespace App\Http\Controllers;

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

        return response()->json(['message' => 'Review submitted for moderation!', 'review' => $review], 201);
    }




    // Get reviews for a property
    public function getPropertyReviews($propertyId)
    {
        $reviews = Review::where('property_id', $propertyId)->get();

        return response()->json($reviews);
    }
    public function deleteReview($reviewId)
    {


        $review = Review::find($reviewId);
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully.'], 200);
    }
}