<?php

namespace App\Http\Controllers;

use App\Http\Resources\OwnerResource;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller

{

    public function getOwnerDetails(Request $request)
    {
        $owner = $request->user(); 
    
        if (!$owner) {
            return response()->json(['error' => 'Owner not authenticated'], 401);
        }
    
        $imageUrl = $owner->image ? url('images/posts/' . $owner->image) : null;  // Full URL for image

        return response()->json([
            'name' => $owner->name,
            'wallet' => $owner->wallet,
            'image' => $imageUrl,
        ]);
    }
    
    
    public function ownerDetails(Request $request)
{
    $owner = $request->user(); 


    $ownerWithPropertiesAndBookings = $owner->load(['properties.booking']);
    
    return response()->json(new OwnerResource($ownerWithPropertiesAndBookings));
}
    public function show($id)
    {
        $owner = Owner::find($id);

        if (!$owner) {
            return response()->json(['message' => 'Owner not found'], 404);
        }

        return response()->json($owner);
    }

    public function updateProfile(Request $request, $id)
    {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:owners,email,' . $id,
            'phone' => 'required|string|min:11|max:15',
            'address' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'gender' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $owner = Owner::findOrFail($id);

        $owner->name = $validatedData['name'];
        $owner->email = $validatedData['email'];
        $owner->phone = $validatedData['phone'];
        $owner->address = $validatedData['address'];
        $owner->company_name = $validatedData['company_name'];
        $owner->gender = $validatedData['gender'];
        $owner->description = $validatedData['description'];

        if (!empty($validatedData['password'])) {
            $owner->password = bcrypt($validatedData['password']);
        }

        if ($request->hasFile('image')) {
        }

        $owner->save();

        return response()->json(['message' => 'Owner profile updated successfully.']);
}
}
