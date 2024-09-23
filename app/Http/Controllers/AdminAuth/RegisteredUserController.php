<?php

namespace App\Http\Controllers\AdminAuth;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:20'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Owner::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'phone' => ['required'],
                'address' => ['required', 'string', 'max:100'],
                'gender' => ['required', 'string', 'max:10'],
                'image' => ['required', 'mimes:jpeg,png,jpg,gif'],
                'role' => ['required', 'string', 'max:10'],
                'description' => ['required', 'string', 'max:100'],
                'company_name' => ['required', 'string', 'max:100'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    
        // Handle image upload
        $image_path = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_path = $image->store('images', 'posts_upload');
        }
    
        // Create the owner
        $owner = Owner::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'phone' => $request->phone,
            'address' => $request->address,
            'gender' => $request->gender,
            'role' => $request->role,
            'description' => $request->description,
            'company_name' => $request->company_name,
            'image' => $image_path
        ]);
    
        // Fire the Registered event
        event(new Registered($owner));
    
        // Create and return token
        $token = $owner->createToken('auth_token')->plainTextToken;
        Auth::guard('owner')->login($owner);
    
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'owner' => $owner
        ], 201);
    }
    
}


