<?php

namespace App\Http\Controllers\AdminAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('owners')->sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 400);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:8',
            'token' => 'required',
        ]);

        $status = Password::broker('owners')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($owner, $password) {
                $owner->password = bcrypt($password);
                $owner->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 400);
    }
    public function sendResetLinkEmailUser(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('users')->sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 400);
    }

    public function resetUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:8',
            'token' => 'required',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_mation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 400);
    }





}
