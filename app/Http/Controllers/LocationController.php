<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function searchLocation(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['error' => 'Query is required'], 400);
        }

        $apiKey = 'AIzaSyDPcD8ze4UcLnj5WOCqXTJW0TUvqIODioA';

        $response = Http::get("https://maps.googleapis.com/maps/api/place/autocomplete/json", [
            'input' => $query,
            'key' => $apiKey,
            'types' => 'geocode',
            'language' => 'en',
        ]);

        return $response->json();
    }
}
