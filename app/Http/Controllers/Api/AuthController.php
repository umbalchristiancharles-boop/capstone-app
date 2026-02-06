<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomerAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Debug: log credentials and password hash
        $user = User::where('username', '=', $credentials['username'])->first();
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

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->update([
            'password' => $request->input('new_password'), // Mutator will hash this
            'must_change_password' => false,
        ]);

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
                'username'  => $u->username ?? null,
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

        $userId = Auth::id();
        
        $validated = $request->validate([
            'fullName' => 'nullable|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'email'    => 'nullable|email|max:255',
            'contact'  => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ]);

        $user = Auth::user();
        $updateData = [
            'full_name' => $validated['fullName'] ?? $user->full_name,
            'email'     => $validated['email'] ?? $user->email,
            'phone_number'     => $validated['contact'] ?? $user->phone_number,
        ];

        // Update username if provided
        if (!empty($validated['username'])) {
            $updateData['username'] = $validated['username'];
        }

        // Update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            $updateData['must_change_password'] = false;
        }

        DB::table('users')
            ->where('id', $userId)
            ->update($updateData);

        // Fetch and return updated user data
        $updatedUser = User::find($userId);
        
        // Generate full absolute URL for avatar if it exists
        $avatarUrl = null;
        if ($updatedUser->avatar_url) {
            if (strpos($updatedUser->avatar_url, 'http') === 0) {
                $avatarUrl = $updatedUser->avatar_url;
            } else {
                $avatarUrl = url($updatedUser->avatar_url);
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'        => $updatedUser->id,
                'username'  => $updatedUser->username ?? null,
                'fullName'  => $updatedUser->full_name ?? null,
                'role'      => $updatedUser->role ?? 'OWNER',
                'email'     => $updatedUser->email ?? null,
                'contact'   => $updatedUser->phone_number ?? null,
                'branch'    => $updatedUser->branch ?? 'Chikin Tayo – QC Main',
                'accountId' => 'kk' . str_pad($updatedUser->id, 5, '0', STR_PAD_LEFT),
                'avatarUrl' => $avatarUrl,
            ],
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

    // Public Authentication Methods for Comments System
    public function sendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        if (User::where('email', '=', $email)->exists()) {
            return response()->json([
                'message' => 'Email already exists. Please sign in instead.'
            ], 409);
        }
        
        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store code in cache for 10 minutes
        Cache::put('verification_code_' . $email, $code, 600);
        
        // Log verification code for debugging
        Log::info("Verification code generated for {$email}: {$code}");
        
        // Send email with verification code
        try {
            Mail::raw(
                "Your CHIKIN TAYO verification code is: {$code}\n\nThis code will expire in 10 minutes.",
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('CHIKIN TAYO - Email Verification Code');
                }
            );
            
            Log::info("Verification email sent to {$email}");
            
            return response()->json([
                'message' => 'Verification code sent to your email',
                'code' => $code, // Always show in development for testing
                'email' => $email
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error("Failed to send verification email to {$email}: " . $e->getMessage());
            
            // In development, show the code anyway
            return response()->json([
                'message' => 'Verification code generated (email system issue - code shown below)',
                'code' => $code,
                'email' => $email,
                'error' => config('app.debug') ? $e->getMessage() : 'Email service unavailable'
            ]);
        }
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = $request->input('email');
        $code = $request->input('code');
        
        // Get stored code
        $storedCode = Cache::get('verification_code_' . $email);
        
        if (!$storedCode) {
            return response()->json([
                'message' => 'Verification code expired. Please request a new one.'
            ], 400);
        }
        
        if ($storedCode !== $code) {
            return response()->json([
                'message' => 'Invalid verification code.'
            ], 400);
        }
        
        // Code is valid - check if user exists
        $userExists = User::where('email', '=', $email)->exists();
        
        // Mark email as verified
        Cache::put('email_verified_' . $email, true, 3600);
        
        return response()->json([
            'message' => 'Email verified successfully',
            'user_exists' => $userExists
        ]);
    }

    public function registerPublic(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'verification_code' => ['required', 'string', 'size:6'],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
            'password.min' => 'Password must be at least 8 characters long.'
        ]);

        $email = $request->input('email');
        
        // Check if email was verified
        if (!Cache::get('email_verified_' . $email)) {
            return response()->json([
                'message' => 'Email not verified. Please verify your email first.'
            ], 400);
        }
        
        // Create user
        $user = User::create([
            'email' => $email,
            'username' => $request->input('username'),
            'password' => $request->input('password'), // Model's setter will hash it
            'email_verified_at' => now(),
            'role' => 'customer', // default role for public registration
        ]);
        
        // Create customer account record
        CustomerAccount::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'full_name' => $request->input('username'), // Use username as initial full_name
            'status' => 'active',
            'last_activity_at' => now(),
        ]);
        
        // Clear verification cache
        Cache::forget('email_verified_' . $email);
        Cache::forget('verification_code_' . $email);
        
        // Create token
        $token = Str::random(60);
        Cache::put('user_token_' . $token, $user->id, 86400 * 30); // 30 days
        
        return response()->json([
            'message' => 'Account created successfully',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
            ],
            'token' => $token
        ], 201);
    }

    public function loginPublic(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', '=', $request->input('username'))
                    ->orWhere('email', '=', $request->input('username'))
                    ->first();
        
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // Create token
        $token = Str::random(60);
        Cache::put('user_token_' . $token, $user->id, 86400 * 30); // 30 days
        
        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
            ],
            'token' => $token
        ]);
    }
}
