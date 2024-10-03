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
use Illuminate\Http\Request;
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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "All fields are mandatory",
                'error' => $validator->messages()
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
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);



        return ApiResponse::sendResponse(200, 'Property added successfully', $property);
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
            ], 422);
        }

        $property = Property::findOrFail($propertyId);
        $property->propertyAmenities()->attach($request->amenities);

        return response()->json(['message' => 'Amenities added successfully.'], 200);
    }



    public function storeImages(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
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
        $property = Property::with(['images', 'amenities'])->find($id);
        return response()->json(
            [
                'message' => 'Property added successfully',
                "data" => [
                    new PropertyResource($property),
                    // $property->propertyImages()->load(['images']),
                    // $property->propertyAmenities()->load(['amenity'])
                ]
            ],
            200
        );
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
            ], 422);
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
        $query = DB::table('properties')
            ->leftJoin('bookings', 'properties.id', '=', 'bookings.property_id')
            ->select('properties.*')
            ->where(function ($query) use ($request) {
                if ($request->has('name')) {
                    $query->where('properties.name', 'like', '%' . $request->input('name') . '%');
                }
                if ($request->has('city')) {
                    $query->where('properties.city', $request->input('city'))->where('properties.status', 'accepted');
                }
                if ($request->has('sleeps')) {
                    $query->where('properties.sleeps', '>=', $request->input('sleeps'))->where('properties.status', 'accepted');
                }
                if ($request->has('bedrooms')) {
                    $query->where('properties.bedrooms', '>=', $request->input('bedrooms'))->where('properties.status', 'accepted');
                }
                if ($request->has(key: 'bathrooms')) {
                    $query->where('properties.bathrooms', '>=', $request->input('bathrooms'))->where('properties.status', 'accepted');
                }
                // if ($request->has('name')) {
                //     $query->where('name', 'like', '%' . $request->input('name') . '%');
                // }
                // if ($request->has('headline')) {
                //     $query->where('headline', 'LIKE', '%' . $request->input('headline') . '%');
                // }
                // if ($request->has('city')) {
                //     $query->where('city', $request->input('city'));
                // }
                // if ($request->has('country')) {
                //     $query->where('country', $request->input('country'));
                // }
                // if ($request->has('address')) {
                //     $query->where('address', $request->input('address'));
                // }
                // if ($request->has('number_of_rooms')) {
                //     $query->where('number_of_rooms', '>=', $request->input('number_of_rooms'));
                // }
                // if ($request->has('amenities')) {
                //     $amenities = explode(',', $request->input('amenities'));
                //     $query->where(function ($q) use ($amenities) {
                //         foreach ($amenities as $amenity) {
                //             $q->orWhere('amenities', 'LIKE', '%' . trim($amenity) . '%');
                //         }
                //     });
                // }
                //   فوزي لو حبيت ممكن تزودهم .
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

            $query->where(function ($query) use ($startDate, $endDate) {
                $query->where('properties.status', 'accepted');

                $query->whereNull('bookings.id')
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('bookings.status', '!=', 'accepted')
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                $query->where('bookings.end_date', '<', $startDate)
                                    ->orWhere('bookings.start_date', '>', $endDate);
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

        return response()->json($properties);
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