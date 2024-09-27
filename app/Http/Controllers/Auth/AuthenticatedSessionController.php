<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $validator = Validator::make($request->all(),([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
               
           ]), [
               'email.required' => 'Email is required',
               'password.required' => 'Password is required',
           ]);
           if($validator->fails()){
               return ApiResponse::sendResponse(200, 'Success', $validator->messages()->all());
           }
   
           if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
               $user = Auth::user();
               $data['token'] = $user->createToken('auth_token')->plainTextToken;
               $data['name'] = $user->name;
               $data['email'] = $user->email;
               return ApiResponse::sendResponse(200, 'Success Login', $data);
           }else{
               return ApiResponse::sendResponse(401, 'user cradentials not match', null);
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
