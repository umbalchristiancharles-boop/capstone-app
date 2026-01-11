<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Basic validation
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Hanapin user batay sa username
        $user = DB::table('users')
            ->where('username', $credentials['username'])
            ->first();

        if (!$user) {
            return response()->json([
                'ok'      => false,
                'message' => 'Invalid username or password',
            ], 401);
        }

        // DEMO ONLY:
        // Direct compare sa password_hash column (plain text, hal. Admin@1234)
        if (!isset($user->password_hash) || $credentials['password'] !== $user->password_hash) {
            return response()->json([
                'ok'      => false,
                'message' => 'Invalid username or password',
            ], 401);
        }

        // Successful login → gamitin Laravel Auth
        Auth::loginUsingId($user->id);
        $request->session()->regenerate();

        return response()->json([
            'ok'      => true,
            'message' => 'Login successful',
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
