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
        $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required'],
            'address' => ['required', 'string', 'max:100', ],
            'gender' => ['required', 'string', 'max:10', ],
            'image' => ['required', 'mimes:jpeg,png,jpg,gif'],

        ]);
        $image_path = null;
        if ($request->hasFile('image')) {
            # code...
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
            'image' => $image_path
        ]);

        event(new Registered($user));
        $token = $user->createToken('auth_token')->plainTextToken;
        Auth::login($user);
  

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }
}


