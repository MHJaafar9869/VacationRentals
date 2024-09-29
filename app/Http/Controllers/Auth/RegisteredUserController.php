<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', Rules\Password::defaults()],
                'phone' => ['required' , 'numeric' , 'max_digits:11' , 'min_digits:11'],
                'address' => ['required', 'string', 'max:100'],
                'gender' => ['required', 'string', 'max:10'],
                'image' => ['required', 'mimes:jpeg,png,jpg,gif'],
            ]);
    
     
            $image_path = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image_path = $image->store('images', 'posts_upload');
            }
    
        
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->string('password')),
                'phone' => $request->phone,
                'address' => $request->address,
                'gender' => $request->gender,
                'image' => $image_path,
            ]);
    
            // Fire the registered event and create token
            event(new Registered($user));
            $token = $user->createToken('auth_token')->plainTextToken;
    
            // Log the user in
            Auth::login($user , true);
    
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(), 
            ], 422);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during registration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}


