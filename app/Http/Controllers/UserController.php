<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function updateProfile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['nullable', 'min:8'],
            'phone' => ['required'],
            'address' => ['required'],
            'gender' => ['required'],
            'image' => ['nullable', 'image'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($user->image) {
                $oldImagePath = public_path('images/user_images/' . $user->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images/user_images'), $imageName);
            $user->image = $imageName;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->address = $request->address;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }
}
