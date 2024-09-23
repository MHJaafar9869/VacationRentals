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
            'email' => ['required'],
            'password' => ['nullable'],
            'phone' => ['required'],
            'address' => ['required'],
            'gender' => ['required'],
            'image' => ['nullable'],
            'company_name' => ['required'],
            'description' => ['required'],

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $Owner = Owner::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($Owner->image) {
                $oldImagePath = public_path('images/user_images/' . $Owner->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/user_images'), $imageName);
            $Owner->image = $imageName;
        }

        $Owner->name = $request->name;
        $Owner->email = $request->email;
        $Owner->phone = $request->phone;
        $Owner->gender = $request->gender;
        $Owner->address = $request->address;
        $Owner->company_name = $request->company_name;
        $Owner->description = $request->description;

        if ($request->filled('password')) {
            $Owner->password = Hash::make($request->password);
        }

        $Owner->save();
        if ($Owner->isEmpty()) {
            return response()->json(['message' => 'Owner not found'], 404);
        }
        return response()->json($Owner);
    }
}
