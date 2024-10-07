<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyResourse;
use App\Models\Amenity;
use App\Models\Category;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyImage;
use Carbon\Carbon;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{


    public function index()
    {
        $property = Property::all()->where('status', '==', 'accepted');
        if (count($property) > 0) {
            return PropertyResource::collection($property);
        } else {
            return response()->json(['message' => 'No record found'], 200);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | min:5 | max:255',
            'headline' => 'required | min:5 | max:255',
            'description' => 'required | min:10',
            'bedrooms' => 'required | integer | min:1',
            'bathrooms' => 'required | integer | min:1',
            'city' => 'required',
            'country' => 'required',
            'address' => 'required',
            'night_rate' => 'required | integer',
            'category_id' => 'required',
            // 'owner_id' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'message' => "All fields are mandatory",
                'error' => $validator->messages()
            ], 422);
        }

        $fullAddress = $request->address . ', ' . $request->city . ', ' . $request->country;
        $coordinates = $this->getCoordinatesFromNominatim($fullAddress);

        if (!$coordinates) {
            return response()->json([
                'message' => 'Unable to get coordinates for the provided address.',
            ], 422);
        }

        $property = Property::create([
            'name' => $request->name,
            'headline' => $request->headline,
            'description' => $request->description,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
            'night_rate' => $request->night_rate,
            'category_id' => $request->category_id,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'owner_id' => Auth::guard('sanctum')->user()->id,
        ]);

        return ApiResponse::sendResponse(200, 'Property added successfully', $property);
    }
    private function getCoordinatesFromNominatim($fullAddress)
    {
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($fullAddress) . "&format=json&limit=1";

        $options = [
            "http" => [
                "header" => "User-Agent: MyAppName/1.0 (email@example.com)"
            ]
        ];

        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);

        if ($response !== false) {
            $json = json_decode($response, true);

            if (!empty($json) && isset($json[0])) {
                return [
                    'latitude' => $json[0]['lat'],
                    'longitude' => $json[0]['lon']
                ];
            }
        }

        return null;
    }
    public function storeAmenities(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'amenities' => 'required|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 200);
        }

        $property = Property::findOrFail($propertyId);
        $property->propertyAmenities()->attach($request->amenities);

        return response()->json(['message' => 'Amenities added successfully.'], 200);
    }

    public function getAmenities()
    {
        $amenities = Amenity::get();
        return response()->json([
            'status' => 200,
            'message' => 'data returned successfully',
            'data' => $amenities
        ]);
    }

    public function storeImages(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:4189',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 400);
        }

        $property = Property::findOrFail($propertyId);

        foreach ($request->file('images') as $image) {
            $path = $image->store('property_images', 'public');

            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
            ]);
        }

        return response()->json(['message' => 'Images uploaded successfully.'], 201);
    }

    public function show($id)
    {

        $property = Property::with(['propertyImages', 'propertyAmenities'])->findOrFail($id);


        return new PropertyResource($property);
        // $property = Property::with(['images', 'amenities'])->find($id);
        // return response()->json(
        //     [
        //         'message' => 'Property added successfully',
        //         "data" => [
        //             new PropertyResource($property),
        //             // $property->propertyImages()->load(['images']),
        //             // $property->propertyAmenities()->load(['amenity'])
        //         ]
        //     ],
        //     200
        // );
    }

    public function update(Request $request, Property $property)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | max:255',
            'headline' => 'required | max:255',
            'description' => 'required',
            'number_of_rooms' => 'required | integer | min:1',
            'image' => 'required',
            'city' => 'required',
            'country' => 'required',
            'address' => 'required',
            'night_rate' => 'required | integer',
            'category_id' => 'required',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'amenities' => 'required|array',
            'amenities.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "All fields are mandatory",
                'error' => $validator->messages()
            ], 200);
        }

        $property->update([
            'name' => $request->name,
            'headline' => $request->headline,
            'description' => $request->description,
            'amenities' => $request->amenities,
            'number_of_rooms' => $request->number_of_rooms,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
            'night_rate' => $request->night_rate,
            'category_id' => $request->category_id,
        ]);

        if ($request->hasFile('images')) {
            $property->images()->delete();

            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('images/properties', 'public');
                $imageUrl = asset('storage/' . $imagePath);
                $property->images()->create(['image_path' => $imageUrl]);
            }
        }

        if ($request->has('amenities')) {
            $property->amenities()->delete();
            foreach ($request->amenities as $amenity) {
                $property->amenities()->create(['amenity' => $amenity]);
            }
        }

        return response()->json(
            [
                'status' => 200,
                'message' => 'Property updated successfully',
                'data' => new PropertyResource($property->load(['images', 'amenities']))
            ],
            200
        );
    }
    public function destroy(Property $property)
    {
        $property->delete();
        return response()->json([
            "message" => "Property deleted successfully"
        ], 200);
    }
    public function search(Request $request)
    {
        $query = Property::with(['category', 'owner', 'booking'])
            ->where(function ($query) use ($request) {
                if ($request->has('name')) {
                    $query->where('name', 'like', '%' . $request->input('name') . '%');
                }
                if ($request->has('city')) {
                    $query->where('city', $request->input('city'))->where('status', 'accepted');
                }
                if ($request->has('sleeps')) {
                    $query->where('sleeps', '>=', $request->input('sleeps'))->where('status', 'accepted');
                }
                if ($request->has('bedrooms')) {
                    $query->where('bedrooms', '>=', $request->input('bedrooms'))->where('status', 'accepted');
                }
                if ($request->has('bathrooms')) {
                    $query->where('bathrooms', '>=', $request->input('bathrooms'))->where('status', 'accepted');
                }
            });

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));

            if ($startDate->isPast() || $endDate->isPast()) {
                return response()->json([
                    'message' => 'The selected date range has passed. Please choose future dates.',
                    'data' => []
                ], 200);
            }

            if ($startDate->gt($endDate)) {
                return response()->json(['message' => 'The start date cannot be after the end date.'], 200);
            }

            if ($endDate->lt($startDate)) {
                return response()->json(['message' => 'The end date cannot be before the start date.'], 200);
            }

            $query->whereDoesntHave('booking', function ($bookingQuery) use ($startDate, $endDate) {
                $bookingQuery->where('status', 'accepted')
                    ->where(function ($dateQuery) use ($startDate, $endDate) {
                        $dateQuery->where('end_date', '>=', $startDate)
                            ->where('start_date', '<=', $endDate);
                    });
            });
        } else {
            return response()->json(['message' => 'Please provide both start and end dates.'], 200);
        }

        $properties = $query->get();

        if ($properties->isEmpty()) {
            return response()->json(['message' => 'No properties found'], 200);
        }

        return response()->json([
            'status' => '200',
            'message' => 'Data returned successfully',
            'data' => PropertyResource::collection($properties),
        ]);
    }

    public function getpropertycategory($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }
        $property = $category->properties;
        return propertyResource::collection($property);
    }

    public function filter(Request $request)
    {
        $request->validate([
            'amenity' => 'required|array',
            'amenity.*' => 'integer|exists:amenities,id',
        ]);

        $amenityIds = $request->input('amenity');

        $properties = Property::whereHas('propertyAmenities', function ($query) use ($amenityIds) {
            $query->whereIn('id', $amenityIds);
        })->get();

        return response()->json(['data' => $properties], 200);
    }

    // public function searchAvailableProperties(Request $request)
    // {
    //     $start_date = $request->start_date;
    //     $end_date = $request->end_date;

    //     $availableProperties = DB::table('properties AS p')
    //         ->whereNotExists(function ($query) use ($start_date, $end_date) {
    //             $query->select(DB::raw(1))
    //                 ->from('booking AS b')
    //                 ->whereColumn('b.property_id', 'p.id')
    //                 ->where('b.status', 'confirmed')
    //                 ->where(function ($query) use ($start_date, $end_date) {
    //                     $query->whereBetween('b.start_date', [$start_date, $end_date])
    //                         ->orWhereBetween('b.end_date', [$start_date, $end_date])
    //                         ->orWhere(function ($query) use ($start_date, $end_date) {
    //                             $query->where('b.start_date', '<=', $start_date)
    //                                 ->where('b.end_date', '>=', $end_date);
    //                         });
    //                 });
    //         })
    //         ->get();

    //     return response()->json($availableProperties);
    // }
}

// if ($request->hasFile('images')) {
//     foreach ($request->file('images') as $image) {
//         $imagePath = $image->store('images/properties', 'public');

//         $imageUrl = asset('storage/' . $imagePath);

//         $property->propertyImages()->create(['image_path' => $imageUrl]);
//     }
// }

// foreach ($request->property_amenities as $amenity_id) {
//     $property->propertyAmenities()->create(['amenity_id' => $amenity_id]);
// }