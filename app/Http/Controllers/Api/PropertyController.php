<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\PropertyResourse;
use App\Models\Category;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $property = Property::all();
        if (count($property) > 0) {
            return PropertyResource::collection($property);
        } else {
            return response()->json(['message' => 'No record found'], 200);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | min:10 | max:255',
            'headline' => 'required | min:10 | max:255',
            'description' => 'required | min:10',
            
            'number_of_rooms' => 'required | integer | min:1',

            'city' => 'required',
            'country' => 'required',
            'address' => 'required',
            'night_rate' => 'required | integer',
            'category_id' => 'required',
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
            'number_of_rooms' => $request->number_of_rooms,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
            'night_rate' => $request->night_rate,
            'category_id' => $request->category_id,
        ]);
        // Store multiple images
        foreach ($request->images as $image) {
            $property->images()->create(['image_path' => $image]);
        }

        // Store multiple amenities
        foreach ($request->amenities as $amenity) {
            $property->amenities()->create(['amenity' => $amenity]);
        }
        return response()->json(
            [
                'message' => 'Property added successfully',
                "data" => new PropertyResource($property)
            ],
            200
        );
        return response()->json($property->load(['images', 'amenities']));
    }
    public function show(Property $property,$id)
    {
        $property = Property::with(['images', 'amenities'])->findOrFail($id);
        return response()->json($property);
        return new PropertyResource($property);
    }
    public function update(Request $request, Property $property)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required | max:255',
            'headline' => 'required | max:255',
            'description' => 'required',
            'amenities' => 'required',
            'number_of_rooms' => 'required | integer | min:1',
            'image' => 'required',
            'city' => 'required',
            'country' => 'required',
            'address' => 'required',
            'night_rate' => 'required | integer',
            'category_id' => 'required',
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
            'image' => $request->image,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
            'night_rate' => $request->night_rate,
            'category_id' => $request->category_id,
        ]);
        return response()->json(
            [
                'message' => 'Property updated successfully',
                "data" => new PropertyResource($property)
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
            ->leftJoin('bookings', 'properties.id', '=', 'bookings.property_id') // Join the booking table
            ->select('properties.*')
            ->where(function ($query) use ($request) {
                // Handle other filters like city, number of rooms, etc.
                if ($request->has('name')) {
                    $query->where('properties.name', 'like', '%' . $request->input('name') . '%');
                }
                if ($request->has('city')) {
                    $query->where('properties.city', $request->input('city'));
                }
                if ($request->has('number_of_rooms')) {
                    $query->where('properties.number_of_rooms', '>=', $request->input('number_of_rooms'));
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
                return response()->json(['message' => 'The selected date range has passed. Please choose future dates.'], 400);
            }
            if ($startDate->gt($endDate)) {
                return response()->json(['message' => 'The start date cannot be after the end date.'], 400);
            }
            if ($endDate->lt($startDate)) {
                return response()->json(['message' => 'The end date cannot be before the start date.'], 400);
            }

            $query->where(function ($query) use ($startDate, $endDate) {
                $query->whereNull('bookings.id')
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('bookings.status', '!=', 'confirmed')
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                $query->where('bookings.end_date', '<', $startDate)
                                    ->orWhere('bookings.start_date', '>', $endDate);
                            });
                    });
            });
        } else {

            return response()->json(['message' => 'Please provide both start and end dates.'], 400);
        }

        $properties = $query->get();

        if ($properties->isEmpty()) {
            return response()->json(['message' => 'No properties found'], 404);
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
}