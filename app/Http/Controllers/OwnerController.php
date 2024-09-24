<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class OwnerController extends Controller
{
    public function updateprofile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['nullable', 'min:8'],
            'phone' => ['required'],
            'address' => ['required'],
            'gender' => ['required'],
            'image' => ['nullable', 'image'],
            'company_name' => ['required'],
            'description' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $owner = Owner::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($owner->image) {
                $oldImagePath = public_path('images/owner_images/' . $owner->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/user_images'), $imageName);
            $owner->image = $imageName;
        }

        $owner->name = $request->name;
        $owner->email = $request->email;
        $owner->phone = $request->phone;
        $owner->gender = $request->gender;
        $owner->address = $request->address;
        $owner->company_name = $request->company_name;
        $owner->description = $request->description;

        if ($request->filled('password')) {
            $owner->password = Hash::make($request->password);
        }

        $owner->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'owner' => $owner
        ], 200);
    }
}
