<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function addToFavorites(Request $request)
    {
        $favorite = Favorite::create([
            'user_id' => Auth::id(),
            'property_id' => $request->property_id,
        ]);

        return response()->json(['message' => 'Property added to favorites!', 'favorite' => $favorite], 201);
    }

    public function removeFromFavorites(Request $request)
    {
        Favorite::where('user_id', Auth::id())
            ->where('property_id', $request->property_id)
            ->delete();

        return response()->json(['message' => 'Property removed from favorites!'], 200);
    }

    public function getUserFavorites()
    {
        $favorites = Favorite::with('properties', '')->where('user_id', Auth::id())->get();

        return FavoriteResource::collection($favorites);
    }
}
