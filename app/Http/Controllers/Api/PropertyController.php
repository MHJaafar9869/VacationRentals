<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePropertyImagesRequest;
use App\Http\Resources\BlockResource;
use App\Http\Resources\BookingDatesResource;
use App\Http\Resources\PropertyResource;
use App\Models\Amenity;
use App\Models\Block;
use App\Models\Booking;
use App\Models\Category;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{


    public function getFirstThree()
    {

        $properties = Property::where('status', '=', 'accepted')->take(3)->get();
        return ApiResponse::sendResponse(200, 'Properties fetched successfully', PropertyResource::collection($properties));
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $page = $request->input('page', 1);

        $propertiesQuery = Property::where('status', '=', 'accepted')->where('show', '=', 'available');

        if ($request->has('limit') && $request->has('page')) {
            $properties = $propertiesQuery->paginate($limit, ['*'], 'page', $page);

            if ($properties->count() > 0) {
                return PropertyResource::collection($properties)->additional([
                    'meta' => [
                        'current_page' => $properties->currentPage(),
                        'from' => $properties->firstItem(),
                        'last_page' => $properties->lastPage(),
                        'path' => $request->url(),
                        'per_page' => $properties->perPage(),
                        'to' => $properties->lastItem(),
                        'total' => $properties->total()
                    ],
                    'links' => [
                        'first' => $properties->url(1),
                        'last' => $properties->url($properties->lastPage()),
                        'prev' => $properties->previousPageUrl(),
                        'next' => $properties->nextPageUrl(),
                    ]
                ]);
            }
        } else {
            $properties = $propertiesQuery->get();

            if ($properties->isNotEmpty()) {
                return PropertyResource::collection($properties);
            }
        }
        return response()->json(['message' => 'No record found'], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | min:5 | max:255',
            'headline' => 'required | min:5 | max:255',
            'description' => 'required | min:10',
            'bedrooms' => 'required | integer | min:1',
            'bathrooms' => 'required | integer | min:1',
            'location' => 'required | min:5 | max:255',
            'night_rate' => 'required | integer',
            'category_id' => 'required',
            'sleeps' => 'required | min:1 | integer',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'message' => "All fields are mandatory",
                'error' => $validator->messages()
            ], 422);
        }

        $fullAddress = $request->location;
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
            'location' => $request->location,
            'sleeps' => $request->sleeps,
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
    public function updateAmenities(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amenities' => 'required|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::findOrFail($id);

        $property->propertyAmenities()->detach();

        $property->propertyAmenities()->attach($request->amenities);

        $updatedAmenities = $property->propertyAmenities()->get();

        return ApiResponse::sendResponse(200, 'Amenities updated successfully', $updatedAmenities);
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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
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
    //TODO Function still in development - BUG: image doesn't get uploaded issue might be in the front-end.
    public function updateImages(UpdatePropertyImagesRequest $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);
        $newImagePaths = [];

        try {
            DB::transaction(function () use ($property, $request, &$newImagePaths) {
                $property->propertyImages()->delete();

                foreach ($request->file('images') as $image) {
                    $path = $image->store('property_images', 'public');

                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image_path' => $path,
                    ]);

                    $newImagePaths[] = $path;
                }
            });

            return response()->json([
                'message' => 'Images updated successfully.',
                'images' => $newImagePaths
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to update images',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id)
    {
        $property = Property::with(['propertyImages', 'propertyAmenities'])->findOrFail($id);
        return new PropertyResource($property);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | min:5 | max:255',
            'headline' => 'required | min:5 | max:255',
            'description' => 'required | min:10',
            'bedrooms' => 'required | integer | min:1',
            'bathrooms' => 'required | integer | min:1',
            'location' => 'required | min:5 | max:255',
            'night_rate' => 'required | numeric',
            'category_id' => 'required',
            'sleeps' => 'required | min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "All fields are mandatory",
                'error' => $validator->messages()
            ], 401);
        }

        $property = Property::find($id);
        if (!$property) {
            return response()->json([
                'code' => 404,
                'message' => 'Property not found',
                'date' => []
            ], 404);
        }

        $property->name = $request->name;
        $property->headline = $request->headline;
        $property->description = $request->description;
        $property->bathrooms = $request->bathrooms;
        $property->bedrooms = $request->bedrooms;
        $property->location = $request->location;
        $property->night_rate = $request->night_rate;
        $property->category_id = $request->category_id;
        $property->sleeps = $request->sleeps;

        try {
            $property->save();
            return response()->json([
                'code' => 200,
                'message' => 'data updated successfully',
                'date' => $property
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
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
            ->where('status', '=', 'accepted');

        if ($request->has('location') && $request->input('location') !== null) {
            $location = $request->input('location');
            $query->where('location', 'LIKE', '%' . $location . '%');
        }

        if ($request->has('sleeps') && $request->input('sleeps') !== null) {
            $query->where('sleeps', '>=', $request->input('sleeps'));
        }

        if ($request->has('price_min') && $request->input('price_min') !== null) {
            $priceMin = $request->input('price_min');
            $query->where('night_rate', '>=', $priceMin);
        }

        if ($request->has('price_max') && $request->input('price_max') !== null) {
            $priceMax = $request->input('price_max');
            $query->where('night_rate', '<=', $priceMax);
        }

        if ($request->has('bedrooms') && $request->input('bedrooms') !== null) {
            $query->where('bedrooms', '>=', $request->input('bedrooms'));
        }

        if ($request->has('bathrooms') && $request->input('bathrooms') !== null) {
            $query->where('bathrooms', '>=', $request->input('bathrooms'));
        }


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


            $query->whereDoesntHave('booking', function ($bookingQuery) use ($startDate, $endDate) {
                $bookingQuery->where(function ($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $endDate)
                            ->where('end_date', '>=', $startDate);
                    });
                });
            });

            $query->whereDoesntHave('blocks', function ($blockQuery) use ($startDate, $endDate) {
                $blockQuery->where(function ($dateQuery) use ($startDate, $endDate) {
                    $dateQuery->where(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $endDate)
                            ->where('end_date', '>=', $startDate);
                    });
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


    public function getSuggestions(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['error' => 'Query is required'], 400);
        }

        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($query) . "&format=json&limit=5&accept-language=en";

        $options = [
            "http" => [
                "header" => "User-Agent: MyAppName/1.0 (email@example.com)"
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            return response()->json(['error' => 'Error fetching suggestions'], 500);
        }

        $json = json_decode($response, true);

        if (!empty($json)) {
            $suggestions = [];
            foreach ($json as $result) {
                $suggestions[] = [
                    'display_name' => $result['display_name'],
                    'lat' => $result['lat'],
                    'lon' => $result['lon'],
                ];
            }
            return response()->json($suggestions);
        }

        return response()->json(['error' => 'No results found'], 404);
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

    public function delete($id)
    {
        $property = Property::find($id);

        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        $property->delete();

        return response()->json(['message' => 'Property deleted successfully']);
    }

    // public function filter(Request $request)
    // {
    //     $request->validate([
    //         'amenity' => 'required|array',
    //         'amenity.*' => 'integer|exists:amenities,id',
    //     ]);
    //     $amenityIds = $request->input('amenity');
    //     $properties = Property::whereHas('propertyAmenities', function ($query) use ($amenityIds) {
    //         $query->whereIn('id', $amenityIds);
    //     })->where('status', '=', 'accepted')->get();
    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Data returned successfully',
    //         'data' => PropertyResource::collection($properties)
    //     ], 200);
    // }
    public function filter(Request $request)
    {
        $request->validate([
            'amenity' => 'required|array',
            'amenity.*' => 'integer|exists:amenities,id',
        ]);

        $amenityIds = $request->input('amenity');
        $numAmenities = count($amenityIds);  // Number of selected amenities

        $properties = Property::whereHas('propertyAmenities', function ($query) use ($amenityIds) {
            // Ensuring that all selected amenities are present
            $query->whereIn('id', $amenityIds);
        }, '=', $numAmenities) // Make sure the count matches the selected number of amenities
            ->where('status', '=', 'accepted')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Data returned successfully',
            'data' => PropertyResource::collection($properties)
        ], 200);
    }


    public function filterByCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|exists:categories,id'
        ]);
        $categoryId = $request->input('category');
        $properties = Property::where('category_id', '=', $categoryId)->where('status', '=', 'accepted')->get();
        return response()->json([
            'status' => 200,
            'message' => 'Data returned successfully',
            'data' => PropertyResource::collection($properties)
        ], 200);
    }

    public function getPropertyAmenities($id)
    {
        $propertyAmenities = PropertyAmenity::where('property_id', '=', $id)->get();
        if (!$propertyAmenities) {
            return response()->json(['message' => 'No record found'], 404);
        }
        return response()->json(['data' => $propertyAmenities], 200);
    }

    public function addBlock(Request $request, $id)
    {
        $user = $request->user();
        if ($user instanceof User) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized: Guests are not allowed to access this data',
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found',
                'data' => [],
            ]);
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Check if the property is already blocked during the selected dates
        $existingBlock = Block::where('property_id', '=', $id)
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('start_date', [$start_date, $end_date])
                    ->orWhereBetween('end_date', [$start_date, $end_date])
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                    });
            })
            ->first();

        if ($existingBlock) {
            return response()->json([
                'status' => 409,
                'message' => 'Property is already blocked for the selected dates',
                'data' => $existingBlock,
            ], 409);
        }

        $block = Block::create([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'property_id' => $id,
        ]);

        return ApiResponse::sendResponse(200, 'Block has been added successfully', $block);
    }
    public function filterPropertiesWithOffer()
{
    $properties = Property::where('offer', '>', 0)
        ->where('status', '=', 'accepted')
        ->get();

    return response()->json([
        'status' => 200,
        'message' => 'Properties with offers returned successfully',
        'data' => PropertyResource::collection($properties)
    ], 200);
}


    public function getBlocksPerProperty($id, Request $request)
    {
        $user = $request->user();

        if ($user instanceof User) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized: Guests are not allowed to access this data',
            ], 403);
        }

        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found',
                'data' => [],
            ], 404);
        }

        $property_blocks = Block::where('property_id', '=', $id)->get();

        if ($property_blocks->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No blocks found for this property',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data retrieved successfully',
            'data' => BlockResource::collection($property_blocks),
        ], 200);
    }

    public function getBookingsByProperty($id, Request $request)
    {
        $bookings = Booking::where('property_id', '=', $id)->get();
        $property = Property::find($id);

        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found',
                'data' => [],
            ], 404);
        }

        $user = $request->user();

        if ($user instanceof User) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized: Guests are not allowed to access this data',
            ], 403);
        }

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No bookings found for this property',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Data retrieved successfully',
            'data' => BookingDatesResource::collection($bookings)
        ], 200);
    }

    public function removeBlock($propertyId, $blockId, Request $request)
    {
        $user = $request->user();

        if (!$user || $user instanceof User) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized: Guests are not allowed to access this data',
            ], 403);
        }

        $property = Property::find($propertyId);
        // dd($propertyId);

        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found',
                'data' => []
            ], 404);
        }

        $block = Block::find($blockId);

        if (!$block) {
            return response()->json([
                'status' => 404,
                'message' => 'Block not found',
                'data' => []
            ], 404);
        }

        $start_date = $block->start_date;
        $end_date = $block->end_date;

        $block->delete();
        return response()->json([
            'status' => 200,
            'message' => "$start_date - $end_date Block has been removed successfully",
            'id' => $blockId
        ], 200);
    }


    public function updateShowProperty($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'show' => 'required|in:available,unavailable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->messages()
            ], 422);
        }

        $validatedData = $validator->validated();

        $property = Property::find($id);
        if (!$property) {
            return response()->json([
                'status' => 404,
                'message' => 'Property not found',
                'data' => [],
            ], 404);
        }

        $property->show = $validatedData['show'];
        $property->save();

        return response()->json([
            'status' => 200,
            'message' => 'Property updated successfully',
            'data' => $property->show,
        ]);
    }


    public function updateOffer(Request $request, Property $property)
    {
        $validator = Validator::make($request->all(), [
            'offer' => 'required|numeric|min:0|max:100',
            'offer_start_date' => 'nullable|date',
            'offer_end_date' => 'nullable|date|after_or_equal:offer_start_date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // If validation passes, update the property
        $data = $validator->validated();

        $property->offer = $data['offer'];
        $property->offer_start_date = $data['offer_start_date'] ?? null;
        $property->offer_end_date = $data['offer_end_date'] ?? null;

        $property->save();

        return response()->json([
            'message' => 'Offer updated successfully',
            'property' => new PropertyResource($property),
        ], 200);
    }
}