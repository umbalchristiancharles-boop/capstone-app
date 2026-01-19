<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;

class AdminPasswordResetController extends Controller
{
    /**
     * Display the admin password reset request form.
     */
    public function showLinkRequestForm()
    {
        // FULL PATH
        return view('auth.admin-forgot-password');
    }

    /**
     * Send admin password reset link to email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Use 'admins' broker for admin-specific password reset
        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Display the admin password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.admin-reset-password', [
            'token' => $token,
            'email' => $request->email ?? old('email'),
        ]);
    }

    /**
     * Reset admin password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use 'admins' broker for admin-specific password reset
        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.dashboard')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
