<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        Log::debug('Login attempt', [
            'username' => $credentials['username'],
            'input_password' => $credentials['password'],
            'db_password' => $user ? $user->password : null,
            'user_exists' => $user ? true : false,
        ]);

        if (!Auth::attempt($credentials)) {    // uses getAuthPassword() if defined
            Log::debug('Auth::attempt failed', [
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
            Log::debug('Auth::user() returned null after attempt', [
                'username' => $credentials['username'],
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'User not found',
            ], 401);
        }

        Log::debug('Login successful', [
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
                'must_change_password' => (bool) $user->must_change_password,
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

    public function profile(Request $request)
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
                'username'  => $u->username,
                'role'      => $u->role,
                'full_name' => $u->full_name,
                'email'     => $u->email,
                'must_change_password' => (bool) $u->must_change_password,
            ],
        ]);
    }

    public function changePassword(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*])[A-Za-z\\d!@#$%^&*]{8,}$/',
                'confirmed',
            ],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password_hash)) {
            return response()->json([
                'ok' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->password_hash = Hash::make($request->input('new_password'));
        $user->must_change_password = false;
        $user->updated_at = now();
        $user->save();

        return response()->json([
            'ok' => true,
            'message' => 'Password updated successfully',
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

        // Generate full absolute URL for avatar if it exists
        $avatarUrl = null;
        if ($u->avatar_url) {
            // If avatar_url doesn't start with http, prepend the full URL
            if (strpos($u->avatar_url, 'http') === 0) {
                $avatarUrl = $u->avatar_url;
            } else {
                $avatarUrl = url($u->avatar_url);
            }
        }

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'        => $u->id,
                'fullName'  => $u->full_name ?? null,
                'role'      => $u->role ?? 'OWNER',
                'email'     => $u->email ?? null,
                'contact'   => $u->phone_number ?? null,
                'branch'    => $u->branch ?? 'Chikin Tayo – QC Main',
                'accountId' => 'kk' . str_pad($u->id, 5, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
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
                'full_name' => $validated['fullName'] ?? $user->full_name,
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

        // Relax validation slightly (allow webp, larger files) and log request for debugging
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ]);

        $user = Auth::user();
        $file = $request->file('avatar');

        if (! $file) {
            Log::debug('uploadAvatar: no file present in request', ['user_id' => $user->id ?? null]);
            return response()->json(['ok' => false, 'message' => 'No file uploaded'], 400);
        }

        try {
            // Generate a unique filename
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store in public/storage/avatars
            $path = $file->storeAs('avatars', $filename, 'public');

            // Update user avatar_url
            $storePath = '/storage/' . $path;
            DB::table('users')
                ->where('id', $user->id)
                ->update(['avatar_url' => $storePath]);

            Log::debug('uploadAvatar: stored avatar', ['user_id' => $user->id, 'path' => $path]);

            return response()->json([
                'ok'        => true,
                'avatarUrl' => url($storePath),
            ]);
        } catch (\Exception $ex) {
            Log::error('uploadAvatar error', ['user_id' => $user->id ?? null, 'exception' => $ex->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to upload avatar'], 500);
        }
    }
}
