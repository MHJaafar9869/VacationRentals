<?php

namespace App\Http\Controllers\AdminAuth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
   
    
    public function store(LoginRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email is required',
            'password.required' => 'Password is required',
        ]);
    
        if ($validator->fails()) {
            return ApiResponse::sendResponse(400, 'Validation failed', $validator->messages()->all());
        }
    
        // Attempt to authenticate the owner
        if (Auth::guard('owner')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('owner')->user();
    
            // Check the user's role
            if ($user->role === 'admin') {
                // Admin-specific response or redirect
                $data['token'] = $user->createToken('auth_token')->plainTextToken;
                $data['role'] = $user->role;
                $data['name'] = $user->name;
                $data['email'] = $user->email;
                return ApiResponse::sendResponse(200, 'Admin Login Success', $data); // You can modify to redirect to admin page
            } elseif ($user->role === 'owner') {
                // Owner-specific response or redirect
                $data['token'] = $user->createToken('auth_token')->plainTextToken;
                $data['role'] = $user->role;
                $data['name'] = $user->name;
                $data['email'] = $user->email;
                return ApiResponse::sendResponse(200, 'Owner Login Success', $data); // Modify to redirect to owner page
            }
        } else {
            return ApiResponse::sendResponse(401, 'User credentials do not match', null);
        }
    }
    
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
