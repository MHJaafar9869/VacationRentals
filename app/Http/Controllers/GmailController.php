<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GmailController extends Controller
{
    // Google login for users
    public function login()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function loginOwner()
    {
        $state = ['role' => 'owner'];

        return Socialite::driver('google')
            ->with(['state' => json_encode($state)])
            ->stateless()
            ->redirect();
    }

    public function redirect(Request $request)
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $state = json_decode($request->input('state'), true);
        $role = $state['role'] ?? 'user';

        if ($role === 'owner') {
            $owner = Owner::updateOrCreate([
                'provider_id' => $googleUser->getId(),
            ], [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'email_verified_at' => now(),
                'image' => $googleUser->getAvatar(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'role' => 'owner',
            ]);

            $token = $owner->createToken('owner_auth_token')->plainTextToken;

            return redirect('http://localhost:4200/login/owner?token=' . $token . '&name=' . urlencode($owner->name) . '&email=' . urlencode($owner->email) . '&role=owner');
        } else {
            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),

            ], [
                'provider_id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'image' => $googleUser->getAvatar(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'email_verified_at' => now(),
                
            ]);

            $token = $user->createToken('YourAppName')->plainTextToken;

            return redirect('http://localhost:4200/login?token=' . $token . '&name=' . urlencode($user->name) . '&email=' . urlencode($user->email) . '&role=user');
        }
    }
}