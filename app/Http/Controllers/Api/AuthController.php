<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Debug: log credentials and password hash
        $user = \App\Models\User::where('username', $credentials['username'])->first();
        \Log::debug('Login attempt', [
            'username' => $credentials['username'],
            'input_password' => $credentials['password'],
            'db_password' => $user ? $user->password : null,
            'user_exists' => $user ? true : false,
        ]);

        if (!Auth::attempt($credentials)) {    // uses getAuthPassword() if defined
            \Log::debug('Auth::attempt failed', [
                'username' => $credentials['username'],
                'input_password' => $credentials['password'],
                'db_password' => $user ? $user->password : null,
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Invalid username or password',
            ], 401);
        }

        $request->session()->regenerate();     // prevent session fixation [web:4][web:6]

        $user = Auth::user();

        if (! $user) {
            \Log::debug('Auth::user() returned null after attempt', [
                'username' => $credentials['username'],
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'User not found',
            ], 401);
        }

        \Log::debug('Login successful', [
            'username' => $user->username,
            'id' => $user->id,
        ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Login successful',
            'user'    => [
                'id'        => $user->id,
                'username'  => $user->username,
                'role'      => $user->role,
                'full_name' => $user->full_name,
            ],
        ]);
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'ok'      => true,
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'ok'   => true,
            'user' => Auth::user(),
        ]);
    }

    public function ownerProfile(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $u = Auth::user();

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'        => $u->id,
                'fullName'  => $u->full_name ?? $u->name ?? null,
                'role'      => $u->role ?? 'OWNER',
                'email'     => $u->email ?? null,
                'contact'   => $u->phone_number ?? null,
                'branch'    => $u->branch ?? 'Chikin Tayo – QC Main',
                'accountId' => 'kk' . str_pad($u->id, 5, '0', STR_PAD_LEFT),
                'avatarUrl' => $u->avatar_url ?? null,
            ],
        ]);
    }

    public function updateOwnerProfile(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validated = $request->validate([
            'fullName' => 'nullable|string|max:255',
            'email'    => 'nullable|email|max:255',
            'contact'  => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'full_name' => $validated['fullName'] ?? $user->full_name ?? $user->name,
                'email'     => $validated['email'] ?? $user->email,
                'phone_number'     => $validated['contact'] ?? $user->phone_number,
            ]);

        return response()->json([
            'ok'      => true,
            'message' => 'Profile updated successfully',
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $file = $request->file('avatar');

        // Generate a unique filename
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        // Store in public/storage/avatars
        $path = $file->storeAs('avatars', $filename, 'public');

        // Update user avatar_url
        DB::table('users')
            ->where('id', $user->id)
            ->update(['avatar_url' => '/storage/' . $path]);

        return response()->json([
            'ok'       => true,
            'avatarUrl' => '/storage/' . $path,
        ]);
    }
}
