<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GmailController extends Controller
{
    //

    public function login(){
        return Socialite::driver('google')->stateless()->redirect();
        }

    public function redirect(){
        $googleUser = Socialite::driver('google')->stateless()->user();
        // dd($googleUser);
                $user = User::updateOrCreate([
            'provider_id' => $googleUser->getId(),
        ], [
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'email_verified_at' => now(),
        ]);
    
        // Create a token for the user
        $token = $user->createToken('YourAppName')->plainTextToken;
    
        // $return response()->json([
        //     'user' => $user,
        //     'image'=> $googleUser->getAvatar(),
        //     'token' => $token,
        // ]);
       
        return redirect('http://localhost:4200/login?token=' . $token . '&name=' . urlencode($user->name) . '&email=' . urlencode($user->email));

    }

}
